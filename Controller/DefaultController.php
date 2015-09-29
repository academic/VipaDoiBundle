<?php
namespace OkulBilisim\OjsDoiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @param string $name
     * @return Response
     */
    public function indexAction($name)
    {
        return $this->render(
            'OjsDoiBundle:Default:index.html.twig',
            array(
                'name' => $name
            )
        );
    }
}
