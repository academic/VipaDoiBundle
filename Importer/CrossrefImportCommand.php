<?php

namespace Vipa\DoiBundle\Importer;

use Vipa\DoiBundle\Entity\CrossrefConfig;
use Doctrine\DBAL\Connection;
use Vipa\ImportBundle\Helper\ImportCommand;
use Vipa\JournalBundle\Entity\Journal;
use Vipa\JournalBundle\Entity\JournalContact;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrossrefImportCommand extends ImportCommand
{
    protected function configure()
    {
        $this->setName('vipa:import:crossref');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('map.oldId, map.newId')
            ->from('ImportBundle:ImportMap', 'map')
            ->where('map.type = :type')
        ;
        $result = $qb->setParameter('type', Journal::class)->getQuery()->getArrayResult();
        $ids = [];

        foreach ($result as $row) {
            $ids[$row['oldId']] = $row['newId'];
        }

        $pluginSql = "SELECT journal_id, setting_name, setting_value FROM plugin_settings ".
            "WHERE plugin_name = 'doipubidplugin' AND journal_id IN (?)";
        $pluginStatement = $this->connection->executeQuery($pluginSql, array(array_keys($ids)), array(Connection::PARAM_INT_ARRAY));

        $settingsSql = "SELECT a.journal_id AS id, a.setting_value AS name, b.setting_value AS pass FROM ".
            " journal_settings a JOIN journal_settings b ON a.journal_id = b.journal_id ".
            " WHERE a.setting_name = 'crossrefUsername' AND a.journal_id IN (?) ".
            " AND b.setting_name = 'crossrefPassword'";
        $settingsStatement = $this->connection->executeQuery($settingsSql, array(array_keys($ids)), array(Connection::PARAM_INT_ARRAY));

        $pluginResult = $pluginStatement->fetchAll();
        $settingsResult = $settingsStatement->fetchAll();
        $settings = [];

        foreach ($pluginResult as $row) {
            $settings[$row['journal_id']][$row['setting_name']] = $row['setting_value'];
        }

        foreach ($settingsResult as $row) {
            $settings[$row['id']]['crossrefUsername'] = $row['name'];
            $settings[$row['id']]['crossrefPassword'] = $row['pass'];
        }

        $counter = 0;

        foreach ($settings as $id => $fields) {
            $journal = $this->em->find('VipaJournalBundle:Journal', $ids[$id]);

            if ($journal) {
                $control = $this->em
                    ->getRepository('VipaDoiBundle:CrossrefConfig')
                    ->findOneBy(['journal' => $journal]);

                if ($control) {
                    continue;
                }
                if(!$fields['crossrefUsername'] || !$fields['crossrefUsername'] || !$fields['doiPrefix']){
                    continue;
                }

                $config = new CrossrefConfig();
                $config->setJournal($journal);
                $config->setPrefix($fields['doiPrefix']);
                $config->setSuffix($fields['doiArticleSuffixPattern']);
                $config->setUsername($fields['crossrefUsername']);
                $config->setPassword($fields['crossrefPassword']);

                /** @var JournalContact $contact */
                if ($contact = $journal->getJournalContacts()->first()) {
                    $config->setFullName($contact->getFullName());
                    $config->setEmail($contact->getEmail());
                }

                $this->em->persist($config);
                $counter++;

                $output->writeln('DOI settings for journal #' . $id . ' imported.');
            }

            if ($counter % 10 == 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        $this->em->flush();
        $this->em->clear();
    }
}
