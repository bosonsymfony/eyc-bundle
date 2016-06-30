<?php

namespace UCI\Boson\EyCBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query;
use Symfony\Component\Security\Acl\Domain\DoctrineAclCache;

/**
 * Nomenclador controller.
 *
 * @Route("/nomenclador")
 */
class NomencladorController extends Controller

{
    private $defaultMaxResults = array(5, 10, 15);

    public function serialize($data, $format = 'json')
    {
        $serializer = $this->get('jms_serializer');
        return $serializer->serialize($data, $format);
    }

    public function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    public function transformQuery($limit, $order)
    {
        $limit = (in_array($limit, $this->defaultMaxResults)) ? $limit : $this->defaultMaxResults[0];
        if ($this->startsWith($order, '-')) {
            return array($limit, substr($order, 1), 'DESC');
        } else {
            return array($limit, $order, 'ASC');
        }
    }


    /**
     * Devuelve todas los Nomencladores
     * Responde al RF51 Listar nomencladores
     * @Route("/", name="eyc_nomenclador_list", options={"expose"=true})
     * @Method("GET")
     *
     */
    public function indexAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $nomencladores = $em->getRepository('EyCBundle:Nomenclador')->createQueryBuilder('e')
            ->getQuery()
            ->getArrayResult();

        $respuesta['data'] = $nomencladores;
        if(count($respuesta['data'])>0){
            $response = new Response($this->serialize($respuesta));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;
        }else{
            return [];
        }

    }

    /**
     * Devuelve un nomenclador dado un id
     * Responde al RF Obtener un nomenclador
     * @Route("/{id}", name="eyc_nomenclador_buscar", options={"expose"=true})
     * @Method("GET")
     *
     */
    public function showNomencladorAction($id)
    {

        $nom = $this->get('nomenclador')->mostrarNom($id);
        if ($nom != NULL) {
            $response = new Response($this->serialize($nom));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;
        } else {
            return new Response('404 GET: El nomenclador solicitado con el identificador ' . $id . ' no existe.');
        }
    }

    /**
     * Crea un Nomenclador
     * Responde al RF47 Adicionar nomenclador
     * @Route("/", name="eyc_nomenclador_create", options={"expose"=true})
     * @Method("POST")
     *
     */
    public function addNomencladorAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $nombre = $request->request->get('nombre');

        $existe = $em->getRepository('EyCBundle:Nomenclador')->findBy(array('nombre'=>$nombre));

        if($existe){
            return new Response('400 POST: El nomenclador que intenta adicionar con el nombre '.$nombre.' ya exite');
        }else{
             $result = $this->get('nomenclador')->insertarNom($nombre);
         if ($result == 1) {
            return new Response('201 POST: El nomenclador se ha creado satisfactoriamente.');
        }
        return new Response('400 POST: Los datos enviados no son validos.');
        }
    }

    /**
     * Edita un Nomenclador
     * Responde al RF48 Modificar nomenclador
     * @Route("/{id}", name="eyc_nomenclador_update", options={"expose"=true})
     * @Method("PUT")
     *
     */
    public function editNomencladorAction(Request $request, $id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $nombre = $request->request->get('nombre');

        $existe = $em->getRepository('EyCBundle:Nomenclador')->findBy(array('nombre'=>$nombre));

        if($existe){
            return new Response('400 POST: Existe un campo con el mismo nombre');

        }else{

            $result = $this->get('nomenclador')->actualizarNom($id, $nombre);

            if ($result == 3) {
                return new Response('400 POST: Los datos enviados no son validos.');
            }

            if ($result == 1) {
                return new Response('200 PUT: El nomenclador fue modificado satisfactoriamente.');
            } else {
                return new Response('404 PUT: El nomenclador solicitado con el identificador ' . $id . ' no existe.');
            }
        }

    }

    /**
     * Buscar nomenclador dado el nombre
     * Responde al RF50 Buscar nomenclador
     * @Route("/buscar_nomenclador/", name="eyc_nomenclador_buscar_nombre", options={"expose"=true})
     * @Method("POST")
     */
    public function buscarNomencladorAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $criterio = $request->request->get('criterio');
        $nomenclador = $em->getRepository('EyCBundle:Nomenclador')->createQueryBuilder('n')
            ->where('n.nombre LIKE :nombre')
            ->setParameter('nombre', '%' . $criterio . '%')
            ->getQuery()
            ->getArrayResult();

        if ($nomenclador != NULL) {
            $response = new Response($this->serialize($nomenclador));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;

        } else {
            $response = new Response($this->serialize($nomenclador));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;
        }
    }


    /**
     * Elimina un Nomenclador
     * Responde al RF49 Eliminar nomenclador
     * @Route("/{id}", name="eyc_nomenclador_delete", options={"expose"=true})
     * @Method("DELETE")
     *
     */
    public function deleteNomencladorAction($id)
    {

        $result = $this->get('nomenclador')->eliminarNom($id);

        if ($result == 1) {

            return new Response('200 Delete: El nomenclador se eliminó con exito.');

        } else {

            return new Response('404 Delete: El nomenclador solicitado con el identificador ' . $id . ' no existe.');

        }

    }

//**********************  Funciones para gestionar los  campos de los nomencladores ***************

    /**
     * Adiciona un campo a un Nomenclador
     * Responde al RF59    Adicionar campo
     * @Route("/{idn}/campo", name="eyc_nomenclador_campo_create", options={"expose"=true})
     * @Method("POST")
     */
    public function addCampoNomencladorAction($idn, Request $request)
    {
        $nombre = $request->request->get('nombre');
        $tipodato = $request->request->get('tipodato');
        $descripcion = $request->request->get('descripcion');
        $nomencladorvin = $request->request->get('nomencladorvin');
        $vinculado = $request->request->get('vinculado');

        if ($vinculado == 'true') {
            $vinculado = true;
        } else {
            $vinculado = false;
        }

        if ($nomencladorvin == '') {
            $nomencladorvin = 0;
        } else {
            $nomencladorvin = (int)$nomencladorvin;
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $existe = $em->getRepository('EyCBundle:CampoNom')->findOneBy(array('nomenclador'=>$idn,'nombre'=>$nombre));

        if($existe){

            return new Response('404 POST: Existe un campo con el mismo nombre');

        }else{

            $result = $this->get('nomenclador')->insertarCampoNom($idn, $nombre, $tipodato, $descripcion, $vinculado, $nomencladorvin);

            if ($result == 3) {
                return new Response('400 POST: El valor del tipo de dato es incorrecto.');
            }

            if ($result == 1) {
                return new Response('201 POST: El nomenclador se ha creado satisfactoriamente.');
            } else {
                return new Response('404 POST: El nomenclador solicitado con el identificador ' . $idn . ' no existe.');
            }
        }
    }

    /**
     * Actualiza los campos de un nomenclador
     * Responde al RF60    Modificar campo
     * @Route("/campo_nom/{idc}", name="eyc_nomenclador_campo_update", options={"expose"=true})
     * @Method("PUT")
     *
     */
    public function updateCampoNomencaldorAction($idc, Request $request)
    {
        $nombre = $request->request->get('nombre');
        $tipodato = $request->request->get('tipodato');
        $descripcion = $request->request->get('descripcion');
        $vinculado = $request->request->get('vinculado');
        $nomencladorvin = $request->request->get('nomencladorvin');

        if ($vinculado == 'true') {
            $vinculado = true;
        } else {
            $vinculado = false;
        }


        if ($nomencladorvin == '') {
            $nomencladorvin = 0;
        } else {
            $nomencladorvin = (int)$nomencladorvin;
        }

        $result = $this->get('nomenclador')->actualizarCampoNom($idc, $nombre, $tipodato, $descripcion, $vinculado, $nomencladorvin);

        if ($result == 3) {
            return new Response('400 PUT: El valor del tipo de dato es incorrecto.');
        }

        if ($result == 1) {
            return new Response('200 PUT: El nomenclador fue modificado satisfactoriamente.');
        } else {
            return new Response('404 PUT: El nomenclador solicitado con el identificador ' . $idc . ' no existe.');
        }
    }


    /**
     * Elimina un  campo de un nomenclador
     * Responde al RF61    Eliminar campo
     * @Route("/campo_nom/{id}", name="eyc_nomenclador_campo_delete", options={"expose"=true})
     * @Method("DELETE")
     */
    public function delCampoNomencladorAction($id)
    {
        $result = $this->get('nomenclador')->eliminarCampoNom($id);
        if ($result == 1) {
            return new Response('200 Delete: El nomenclador se eliminó con exito');
        } else
            return new Response('404 Delete: El nomenclador solicitado con el identificador ' . $id . ' no existe.');
    }

    /**
     * Devuelve el campo dado el id
     * Responde al RF62 Listar campos de nomenclador
     * @Route("/campo_nom/{id}", name="eyc_nomenclador_campos", options={"expose"=true})
     * @Method("GET")
     *
     */
    public function showCamposNomencladorAction($id, Request $request)
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $order = $request->get('order', 'id');
        list($limit, $order, $direction) = $this->transformQuery($limit, $order);
        $em = $this->get('doctrine.orm.entity_manager');
        $camposnom = $em->getRepository('EyCBundle:CampoNom')->createQueryBuilder('e')
            ->where('e.nomenclador =:id')
            ->setParameter('id', $id)
            ->orderBy('e.' . $order, $direction)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        if (count($camposnom) == 0 || $camposnom == NULL) {
            return new Response('404 GET: El nomenclador solicitado con el identificador ' . $id . ' no existe.');
        } else {
            $paginator = new Paginator($camposnom);
            $count = $paginator->count();

            $respuesta = array(
                'data' => $camposnom->getArrayResult(),
                'count' => $count
            );
            $response = new Response($this->serialize($respuesta));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;
        }

    }

    /**
     * Devuelve el campo dado el id
     * Responde al RF62 Listar campos de nomenclador
     * @Route("/campo_nom/{id}/sin_filtro", name="eyc_nomenclador_campos2", options={"expose"=true})
     * @Method("GET")
     *
     */
    public function showCamposNomenclador2Action($id)
    {

        $em = $this->get('doctrine.orm.entity_manager');
        $camposnom = $em->getRepository('EyCBundle:CampoNom')->createQueryBuilder('e')
            ->where('e.nomenclador =:id')
            ->setParameter('id', $id)
            ->getQuery();

        if (count($camposnom) == 0 || $camposnom == NULL) {
            return new Response('404 GET: El nomenclador solicitado con el identificador ' . $id . ' no existe.');
        } else {
            $paginator = new Paginator($camposnom);
            $count = $paginator->count();

            $respuesta = array(
                'data' => $camposnom->getArrayResult(),
                'count' => $count
            );
            $response = new Response($this->serialize($respuesta));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;
        }

    }

    function eliminarCaracteresInvalidos($cadena){
        $cadena = str_replace(" ","",$cadena);
        $cadena = str_replace("ã¡","a",$cadena);
        $cadena = str_replace("ã©","e",$cadena);
        $cadena = str_replace("ã­","i",$cadena);
        $cadena = str_replace("ã³","o",$cadena);
        $cadena = str_replace("ãº","u",$cadena);
        $cadena = str_replace("ã±","n",$cadena);

        return $cadena;
    }


    /**
     * @Route("/{id}/campos_vinculados", name="eyc_nomenclador_campos_vinculados", options={"expose"=true})
     * @Method("GET")
     *
     */
    public function showCamposNomencladorVinculadoAction($id)
    {

        $em = $this->get('doctrine.orm.entity_manager');
        $camposnom = $em->getRepository('EyCBundle:CampoNom')->createQueryBuilder('e')
            ->where('e.nomenclador =:id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getArrayResult();

        if (count($camposnom) == 0 || $camposnom == NULL) {
            return new Response('404 GET: El nomenclador solicitado con el identificador ' . $id . ' no existe.');
        } else {

            $campoVinculados = array();

            for($i = 0; $i < count($camposnom);$i++){
               if($camposnom[$i]['nomencladorVin'] != 0){
                 $nombre =  $this->eliminarCaracteresInvalidos(utf8_encode(strtolower($camposnom[$i]['nombre']))) ;

                    $campoVinculados[$nombre] = $this->get('nomenclador')->mostrarNomOp2($camposnom[$i]['nomencladorVin'])['valores'];
               }//
            }


            $response = new Response($this->serialize($campoVinculados));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;
        }

    }


    //********************************************** NomencladorOP *************************************************

    /**
     * Mostrar una instancia de un nomenclador.
     * Responde al RF71 Listar instancias a nomenclador
     * @Route("/nom_op_all/{id}", name="eyc_nomenclador_instancias", options={"expose"=true})
     * @Method("GET")
     */
    public function showAllNomencladorOpAction($id, Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $camposnom = $em->getRepository('EyCBundle:CampoNom')->createQueryBuilder('e')
            ->where('e.nomenclador =:id')
            ->setParameter('id', $id)
            ->orderBy('e.id', 'ASC')
            ->getQuery()
            ->getArrayResult();


//        $paginator = new Paginator($camposnom);
//        $count = $paginator->count();
        $cantCampos = count($camposnom);
        $filter = $request->get('filter', "");
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $order = $request->get('order', 'id');
        list($limit, $order, $direction) = $this->transformQuery($limit, $order);
        $nomOps = $this->get('nomenclador')->mostrarNomOp($id, $order, $page, $limit, $filter, $cantCampos, $direction);


        if ($nomOps != null) {
            $respuesta = $nomOps;

            if ($respuesta != NULL || count($respuesta)) {
                $response = new Response($this->serialize($respuesta));
                $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
                return $response;
            }
        } else {
            return new Response('404 GET: El nomenclador solicitado con el identificador ' . $id . ' no existe.');
        }
    }


    /**
     * Mostrar todas las instancias de un nomenclador.
     * Responde al RF Mostrar una instancia de un nomenclador.
     * @Route("/nom_op/{idnop}", name="eyc_nomenclador_instancia_buscar", options={"expose"=true})
     * @Method("GET")
     */
    public function showNomencladorOpAction($idnop)
    {
        $nomencop = $this->get('nomenclador')->mostrarNomeOp($idnop);
        if ($nomencop == false) {
            return new Response('404 GET: El nomenclador solicitado con  identificador ' . $idnop . ' no existe.');
        } else {
            $response = new Response($this->serialize($nomencop));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;

        }

    }

    /**
     * Buscar nomencladorOP dado el nombre
     * Responde al RF50 Buscar nomenclador
     * @Route("/buscar_nomencladorop/", name="eyc_nomenclador_instancia_buscar_nombre", options={"expose"=true})
     * @Method("POST")
     */
    public function buscarNomOp(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $criterio = $request->request->get('criterio');
        $idn = $request->request->get('idn');
        $respuesta = array();
        $result = array();

        $idNomOPS = $this->get('nomenclador')->mostrarNomeOpsByNomenclador($idn);//me da los id de los nop dado un idn

        if (count($idNomOPS) == 1) {
            $response = new Response($this->serialize($result));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;
        }

        foreach ($idNomOPS as $id) {

            $result = $em->getRepository('EyCBundle:NomencladorOp')->buscarNomOp($id['id_nop'], $criterio);//obtiene campos valores

            if (count($result) > 0) {
                $respuesta[$id['nombre']] = $result;
            }
        }

        if ($respuesta != NULL) {
            $response = new Response($this->serialize($respuesta));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;

        } else {
            $response = new Response($this->serialize($respuesta));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;
        }
    }


    /**
     * Adiciona una instancia de un Nomenclador
     * Responde al RF67 Adicionar instancia a nomenclador
     * @Route("/nom_op/{idn}",name="eyc_nomenclador_instancia_create", options={"expose"=true})
     * @Method("POST")
     */
    public function addNomencladorOPAction($idn, Request $request)
    {
        $camposnom = $request->request->get('camposnom');
        $valores = $request->request->getIterator('valores');

        $result = $this->get('nomenclador')->insertarNomOp($idn, $camposnom, $valores['valores']);
        switch ($result) {
            case 1:
                return new Response('201 POST: El nomenclador se ha creado satisfactoriamente.');
                break;
            case 2:
                return new Response('400 POST: Los valores insertados no coincide con los campos del nomenclador.');
                break;
            case 3:
                return new Response('400 POST: El arreglo de valores no coincide con los campos del nomenclador.');
                break;
            case 4:
                return new Response('400 POST: El arreglo de valores no tiene la estructura esperada.');
                break;
            case 5:
                return new Response('400 POST: No se puede obtener una instancia del nomenclador especificado.');
                break;
        }

    }


    /**
     * Modifica una instancia de un Nomenclador
     * Responde al RF68 Modificar instancia a nomenclador
     * @Route("/nom_op/{idnop}", name="eyc_nomenclador_instancia_update", options={"expose"=true})
     * @Method("PUT")
     */
    public function updateNomencladorOPAction($idnop, Request $request)
    {
        $camposnom = $request->request->get('camposnom');
        $valores = $request->request->getIterator('valores');
        $result = $this->get('nomenclador')->actualizarNomOp($idnop, $camposnom, $valores['valores']);

        switch ($result) {
            case 1:
                return new Response('200 PUT: El nomenclador fue modificado satisfactoriamente.');
                break;
            case 2:
                return new Response('400 PUT: Los valores insertados no coincide con los campos del nomenclador.');
                break;
            case 3:
                return new Response('400 PUT: El arreglo de valores no coincide con los campos del nomenclador.');
                break;
            case 4:
                return new Response('400 PUT: El arreglo de valores no tiene la estructura esperada.');
                break;
            case 5:
                return new Response('400 PUT: No se puede obtener campos para la instancia del nomenclador especificada');
                break;
            case 6:
                return new Response('404 PUT: No se puede obtener una instancia del nomenclador especificado.');
                break;
        }
    }


    /**
     * Elimina una instancia de un Nomenclador
     * Responde al RF69 Eliminar instancia a nomenclador
     * @Route("/nom_op/{idnop}", name="eyc_nomenclador_instancia_delete", options={"expose"=true})
     * @Method("DELETE")
     */
    public function deleteNomencladorOPAction($idnop)
    {
        $result = $this->get('nomenclador')->eliminarNomOp($idnop);
        if ($result == 1) {

            return new Response('200 Delete: El nomenclador se eliminó con exito');

        } else

            return new Response('404 Delete: El campo de la estructura  con el identificador ' . $idnop . ' no existe.');
    }

}
