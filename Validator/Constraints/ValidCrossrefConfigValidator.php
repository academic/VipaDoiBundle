<?php

namespace Ojs\DoiBundle\Validator\Constraints;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidCrossrefConfigValidator extends ConstraintValidator
{
    /**
     * @param \Ojs\DoiBundle\Entity\CrossrefConfig $crossrefConfig
     * @param Constraint $constraint
     */
    public function validate($crossrefConfig, Constraint $constraint)
    {
        /** @var ValidCrossrefConfig $constraint */
        $client = new Client();

        try {
            $result = $client->request(
                'GET',
                'http://doi.crossref.org/info?usr='.$crossrefConfig->getUsername().'&pwd='.$crossrefConfig->getPassword(
                ).'&rtype=prefixes'
            );
            $jsonResult = json_decode($result->getBody()->getContents(), true);
            $allowedPrefixes = $jsonResult['allowed-prefixes'];
            if (!in_array($crossrefConfig->getPrefix(), $allowedPrefixes, true)) {
                $this->context->buildViolation(sprintf($constraint->prefixMessage, implode(',', $allowedPrefixes)))
                    ->atPath($constraint->prefix)
                    ->addViolation();
            }
        } catch (RequestException $e) {
            if (strpos($e->getMessage(), 'does not seem to exist') !== false) {
                $this->context->buildViolation($constraint->usernameMessage)
                    ->atPath($constraint->username)
                    ->addViolation();
            } else {
                $this->context->buildViolation($constraint->passwordMessage)
                    ->atPath($constraint->password)
                    ->addViolation();
            }
        }

    }
}
