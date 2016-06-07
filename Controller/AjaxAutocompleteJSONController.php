<?php

namespace PUGX\AutocompleterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Symfony\Component\HttpFoundation\Response;

class AjaxAutocompleteJSONController extends Controller
{

    public function searchAction($entityAlias)
    {
        $entities = $this->get('service_container')->getParameter('pugx_autocompleter.autocomplete_entities');
        $entityInfo = $entities[$entityAlias];

        $em = $this->get('doctrine')->getManager();
        $request = $this->getRequest();

        $letters = $request->get('term');
        $maxRows = $request->get('maxRows');

        if ($entityInfo['role'] !== 'IS_AUTHENTICATED_ANONYMOUSLY'){
            if (false === $this->get('security.context')->isGranted($entityInfo['role'])) {
                throw new AccessDeniedException();
            }
        }

        if (isset($entityInfo['custom_search']) && $entityInfo['custom_search']) {

            return $this->forward($entityInfo['custom_search'], array('entityInfo' => $entityInfo));
        }

        switch ($entityInfo['search']){
            case "begins_with":
                $like = $letters . '%';
            break;
            case "ends_with":
                $like = '%' . $letters;
            break;
            case "contains":
                $like = '%' . $letters . '%';
            break;
            default:
                throw new \Exception('Unexpected value of parameter "search"');
        }

	    $property = $entityInfo['property'];
        $class    = $entityInfo['class'];

        $repo = $em->getRepository($class);

        $qb   = $repo->createQueryBuilder('q');

        $result = $qb->where($qb->expr()->like('q.' . $property, '?1'))
                     ->setParameter(1, $like)
                     ->getQuery()
                     ->getArrayResult();

        $output = array();

        foreach ($result AS $r){

            $output[] = ['id' => $r['id'], 'label' => $r[$property], 'value' => $r[$property]];

        }

        return new JsonResponse($output);

    }

    public function getAction($entityAlias)
    {
        $em = $this->get('doctrine')->getManager();
        $request = $this->getRequest();

        $entities = $this->get('service_container')->getParameter('pugx_autocompleter.autocomplete_entities');

        $id           = $request->get('id');

        $entityInfo = $entities[$entityAlias];

        if ($entityInfo['role'] !== 'IS_AUTHENTICATED_ANONYMOUSLY'){

            if (false === $this->get('security.context')->isGranted($entityInfo['role'])) {
                throw new AccessDeniedException();
            }
        }

        if (isset($entityInfo['custom_get']) && $entityInfo['custom_get']) {

            return $this->forward($entityInfo['custom_get'], array('entityInfo' => $entityInfo));
        }

        $class      = $entityInfo['class'];
        $property   = $entityInfo['property'];

        $item = $em->getRepository($class)->find($id);

        $accessorMethod = 'get' . ucwords($property);

        if (!method_exists($item, $accessorMethod)) {

            throw new \Exception('Accessor method for property ' . $property . ' doesn\'t exist');
        }

        return new Response($item->$accessorMethod());

    }
}
