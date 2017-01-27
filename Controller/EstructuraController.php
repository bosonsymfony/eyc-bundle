<?php

namespace UCI\Boson\EyCBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


/**
 * Estructura controller.
 *
 * @Route("/estructura")
 */
class EstructuraController extends Controller
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

//////////////////////////////////////////////// // Estas funciones gestionan las estructuras////////////////////

    /**
     * Devuelve todas las estructuras
     * Responde al RF56 Listar estructuras
     * @Route("/", name="eyc_estructura_list", options={"expose"=true})
     * @Method("GET")
     *
     */
    public function indexAction(Request $request)
    {
        $filter = $request->get('filter', '');
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $order = $request->get('order', 'id');
        list($limit, $order, $direction) = $this->transformQuery($limit, $order);
        $em = $this->get('doctrine.orm.entity_manager');
        $estructura = $em->getRepository('EyCBundle:Estructura')->createQueryBuilder('e')
            ->orderBy('e.' . $order, $direction)
            ->setFirstResult(($page - 1) * $limit)
            ->orWhere("e.nombre LIKE '%$filter%'")
            ->setMaxResults($limit)
            ->getQuery();;

        $paginator = new Paginator($estructura);
        $count = $paginator->count();

        $respuesta = array(
            'data' => $estructura->getArrayResult(),
            'count' => $count
        );

        $response = new Response($this->serialize($respuesta));
        $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
        return $response;

    }

    /**
     * Devuelve todas las estructuras
     * Responde al RF56 Listar estructuras
     * @Route("/sin_filtro", name="eyc_estructura_list2", options={"expose"=true})
     * @Method("GET")
     *
     */
    public function index2Action(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $estructura = $em->getRepository('EyCBundle:Estructura')->createQueryBuilder('e')
            ->getQuery()
            ->getArrayResult();


        $numCampos = array();
        for($i = 0; $i < count($estructura);$i++){
            if($estructura[$i]['raiz'] == 1){
                $ide = $estructura[$i]['id'];
                $camposestruc = $em->getRepository('EyCBundle:CampoEstruc')->createQueryBuilder('e')
                    ->where('e.estructura =:id')
                    ->setParameter('id', $ide)
                    ->getQuery()
                    ->getArrayResult();
                array_push($numCampos,count($camposestruc));
            }
        }

        $respuesta = array(
            'data' => $estructura,
            'campos' => $numCampos
        );

        $response = new Response($this->serialize($respuesta));
        $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
        return $response;
    }

    /**
     * Crea una estructura
     * Responde al RF52 Adicionar estructura
     * @Route("/", name="eyc_estructura_create", options={"expose"=true})
     * @Method("POST")
     *
     */
    public function createEstructuraAction(Request $request)
    {
        $nombre = $request->request->get('nombre');
        $raiz = $request->request->get('raiz');
        $em = $this->get('doctrine.orm.entity_manager');

        if ($raiz == 'true') {
            $raiz = true;

        } else {
            $raiz = false;
        }

        $existe = $em->getRepository('EyCBundle:Estructura')->findBy(array('nombre' => $nombre));


        if ($existe) {
            return new Response('400 POST: Existe una estructura con el mismo nombre.');
        } else {
            if (is_bool($raiz)) {
                $this->get('estructura')->insertarEstruc($nombre, $raiz);
                return new Response('201 POST: La estructura se ha creado satisfactoriamente.');
            } else {
                return new Response('422 InvIntPUT: El sistema esperaba un booleano, y se recibió ' . $raiz . '.');
            }
        }
    }

    public function cantHijas($id){
        $em = $this->get('doctrine.orm.entity_manager');
        $hijos = $em->createQueryBuilder()
            ->select('estructura', 'estructuras_hijas')
            ->from('EyCBundle:Estructura', 'estructura')
            ->join('estructura.estructurasHijas', 'estructuras_hijas')
            ->where("estructura.id = $id")
            ->getQuery()
            ->getArrayResult();

        return count($hijos);
    }

    /**
     * Actualiza un estructura dado el id
     * Responde al RF53 Modificar estructura
     * @Route("/{id}",name="eyc_estructura_update", options={"expose"=true})
     * @Method("PUT")
     *
     */
    public function updateEstructuraAction(Request $request, $id)
    {
        $nombre = $request->request->get('nombre');
        $raiz = $request->request->get('raiz');

        if ($raiz == 'true') {
            $raiz = true;

        } else {
            $raiz = false;
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $existe = $em->getRepository('EyCBundle:Estructura')->createQueryBuilder('e')
            ->orWhere("e.nombre LIKE '$nombre'")
            ->getQuery()
            ->getArrayResult();

//            return new Response('400 POST: Un nodo hijo no puede ser raiz.');

        $estructura = $em->getRepository('EyCBundle:Estructura')->find($id);

        if(count($estructura->getEstructurasPadres())&&$raiz==true){
            return new Response('400 PUT: La estructura no puede ser raiz debido a que es hija de otra estructura.');
        }


        if ($existe && $existe[0]['id'] != $id) {
              return new Response('400 POST: Existe una estructura con el mismo nombre.');
        } else {
            if (is_bool($raiz)) {
                $result = $this->get('estructura')->actualizarEstruc($id, $nombre, $raiz);

                if ($result == 1) {

                    return new Response('200 PUT: La estructura fue modificado satisfactoriamente.');

                } else {

                    return new Response('404 PUT: La estructura solicitado con el identificador ' . $id . ' no existe.');

                }
            } else {
                return new Response('404 PUT: El valor de la raiz tiene que ser de tipo booleano');
            }
        }
    }

    /**
     * Elimina una estructura dado el id
     * Responde al RF54 Eliminar estructura
     * @Route("/{id}",name="eyc_estructura_delete", options={"expose"=true})
     * @Method("DELETE")
     */
    public function deleteAction($id)
    {
        $result = $this->get('estructura')->eliminarEstruc($id);
        if ($result == 1) {

            return new Response('200 Delete: La estructura se eliminó con exito');

        } else {

            return new Response(' 404 Delete: La estructura solicitado con el identificador ' . $id . ' no existe.');

        }
    }



    /**
     * Elimina una estructura hija dado el id del padre
     * Responde al RF58 Eliminar relación entre estructuras
     * @Route("/{id_padre}/hijas", name="eyc_estructura_delete_hijas", options={"expose"=true})
     * @Method("DELETE")
     */
    public function deleteHijasAction($id_padre, Request $request)
    {
        $id_hija = $request->request->get('hija');

        $result = $this->get('estructura')->eliminarRelacionEstructura($id_hija, $id_padre);

        switch ($result) {
            case 1:
                return new Response('200 DELETE: La estructura se ha eliminado satisfactoriamente.');
                break;
            case 2:
                return new Response('400 DELETE: La estructura solicitado con id ' . $id_padre . ' no existe');
                break;
            case 3:
                return new Response('400 DELETE: La estructura solicitado con id ' . $id_hija . ' no existe');
                break;
            case 4:
                return new Response('400 DELETE: La estructura con id ' . $id_hija . ' no se encuentra entre las estructuras hijas de la estructura con id ' . $id_padre . ' .');
                break;
        }
    }


    /**
     * Adiciona hijos a una estructura padre
     * Responde al RF57 Crear relación entre estructuras
     * @Route("/{id}/addHijas",name="eyc_estructura_add_hijas", options={"expose"=true})
     * @Method("POST")
     *
     */
    public function addHijasAction($id, Request $request)
    {

        $ids = $request->request->getIterator('ids');
        $result = $this->get('estructura')->insertarEstrucSub($id, $ids);

        if ($result == 4) {
            return new Response('201 POST: La estructura se ha creado satisfactoriamente.');
        } else {

            if ($result == 1) {
                return new Response('404 POST: Una de las estructuras subordinadas indicadas no existe.');
            }

            if ($result == 2) {
                return new Response('404 POST: La estructura con id ' . $id . ' a la que intenta adicionarle subordinaciones no existe.');
            }

            if ($result == 3) {
                return new Response('400 POST: El formato de los datos no son validos.');

            }
        }
    }

    /**
     * Obtiene una estructura dado el id
     * Responde al RF Obtener estructura
     * @Route("/{id}",name="eyc_estructura_buscar_estructura", options={"expose"=true})
     * @Method("GET")
     */
    public function showAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $estructura = $em->getRepository('EyCBundle:Estructura')->createQueryBuilder('e')
            ->where('e.id =:id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getArrayResult();

        if ($estructura != NULL) {
            $response = new Response($this->serialize($estructura));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;

        } else {
            return new Response('404 GET: La estructura solicitado con el identificador ' . $id . ' no existe.');
        }

    }

    /**
     * Buscar estructura dado el nombre
     * Responde al RF55 Buscar estructura
     * @Route("/buscar_estructura/",name="eyc_estructura_buscar_estructura_nomb", options={"expose"=true})
     * @Method("POST")
     */
    public function buscarEstructuraAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $criterio = $request->request->get('criterio');
        $estructura = $em->getRepository('EyCBundle:Estructura')->createQueryBuilder('e')
            ->where('e.nombre LIKE :nombre')
            ->setParameter('nombre', '%' . $criterio . '%')
            ->getQuery()
            ->getArrayResult();

        if ($estructura != NULL) {
            $response = new Response($this->serialize($estructura));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;

        } else {
            $response = new Response($this->serialize($estructura));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;
        }
    }

    /**
     * Devuelve las estructuras hijas de una estructura
     * Responde al RF Obtener estructuras hijas
     * @Route("/{id}/hijos", name="eyc_estructura_hijas", options={"expose"=true})
     * @Method("GET")
     */
    public function showHijosEstructuraAction($id, Request $request)
    {
        $limit = $request->get('limit', 5);
        $page = $request->get('page', 1);
        $order = $request->get('order', "id");
        list($limit, $order, $direction) = $this->transformQuery($limit, $order);
        $em = $this->get('doctrine.orm.entity_manager');

        $hijos = $em->createQueryBuilder()
            ->select('estructura', 'estructuras_hijas')
            ->from('EyCBundle:Estructura', 'estructura')
            ->join('estructura.estructurasHijas', 'estructuras_hijas')
            ->where("estructura.id = $id")
            ->orderBy('estructuras_hijas.' . $order, $direction)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();


        $numCampos = array();
        for($i = 0; $i < count($hijos[0]['estructurasHijas']);$i++){
            $ide = $hijos[0]['estructurasHijas'][$i]['id'];
            $camposestruc = $em->getRepository('EyCBundle:CampoEstruc')->createQueryBuilder('e')
                ->where('e.estructura =:id')
                ->setParameter('id', $ide)
                ->getQuery()
                ->getArrayResult();
            array_push($numCampos,count($camposestruc));
        }

        if (count($hijos) > 0) {
            $respuesta = array(
                'data' => $hijos,
                'campos'=>$numCampos,
                'count' => count($hijos[0]['estructurasHijas'])
            );

            $response = new Response($this->serialize($respuesta));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*', 'order' => $order));
            return $response;
        } else {
            return new Response('404 GET: La estructura solicitado con el identificador ' . $id . ' no existe.');
        }
    }

    /**
     * @Route("/est_op/{id}/hijos", name="eyc_estructura_instancias_hijas", options={"expose"=true})
     * @Method("GET")
     */
    public function showHijosEstructuraOPAction($id, Request $request)
    {
        $filter = $request->get('filter', '');
        $limit = $request->get('limit', 5);
        $page = $request->get('page', 1);
        $order = $request->get('order', "id");
        list($limit, $order, $direction) = $this->transformQuery($limit, $order);
        $em = $this->get('doctrine.orm.entity_manager');

        $hijos = $em->getRepository('EyCBundle:EstructuraOp')->createQueryBuilder('op')
            ->where("op.parent = $id")
            ->orderBy('op.' . $order, $direction)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();
//        print_r($hijos);

        if (count($hijos) > 0) {
            $valores = array();
            for ($i = 0; $i < count($hijos); $i++) {
                $result = $em->getRepository('EyCBundle:EstructuraOp')->findEstrucOpID($hijos[$i]['id']);//me da campo y valores estOP
                if ($filter !== '') {
                    if (strpos(strtolower($result[0]['NomEOP']), strtolower($filter)) !== false) {
                        array_push($valores, $result[0]);
                    }
                } else {
                    array_push($valores, $result[0]);
                }
            }
//            print_r($valores);
            $respuesta = array(
                'valores' => $valores,
                'count' => count($valores)
            );

            $response = new Response($this->serialize($respuesta));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*', 'order' => $order));
            return $response;
        } else {
            return new Response('404 GET: La estructura solicitado con el identificador ' . $id . ' no existe.');
        }
    }

    /**
     * @Route("/est_op/estrucOpRaiz", name="eyc_estructura_instancias_hijas2", options={"expose"=true})
     * @Method("GET")
     */
    public function showHijosEstructuraO2PAction(Request $request)
    {
        $filter = $request->get('filter', '');
        $limit = $request->get('limit', 5);
        $page = $request->get('page', 1);
        $order = $request->get('order', "id");
        list($limit, $order, $direction) = $this->transformQuery($limit, $order);
        $em = $this->get('doctrine.orm.entity_manager');
        $estructura = $em->getRepository('EyCBundle:Estructura')->createQueryBuilder('e')
            ->getQuery()
            ->getArrayResult();

        $valores = array();
        for ($i = 0; $i < count($estructura); $i++) {
            if ($estructura[$i]['raiz']) {
                $id = $estructura[$i]['id'];
                $hijos = $em->getRepository('EyCBundle:EstructuraOp')->createQueryBuilder('op')
                    ->where("op.estructura = $id")
//                ->orderBy('op.' . $order, $direction)
//                ->setFirstResult(($page - 1) * $limit)
//                ->setMaxResults($limit)
                    ->getQuery()
                    ->getArrayResult();
//        print_r($hijos);
//exit();
                if (count($hijos) > 0) {

                    for ($j = 0; $j < count($hijos); $j++) {
                        $result = $em->getRepository('EyCBundle:EstructuraOp')->findEstrucOpID($hijos[$j]['id']);//me da campo y valores estOP

                        if ($filter !== '') {
                            if (strpos(strtolower($result[0]['NomEOP']), strtolower($filter)) !== false) {
                                array_push($valores, $result[0]);
                            }
                        } else {
                            array_push($valores, $result[0]);
                        }
                    }
                }
            }
        }
        $respuesta = array(
            'valores' => $valores,
            'count' => count($valores)
        );
        if (count($valores) > 0) {
            $response = new Response($this->serialize($respuesta));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*', 'order' => $order));
            return $response;
        } else {
            return new Response('404 GET: La estructura solicitado con el identificador ' . $id . ' no existe.');
        }
    }


    /**
     * Devuelve la estructura padre de una estructura
     * @Route("/{id}/padre", name="eyc_estructura_padre", options={"expose"=true})
     * @Method("GET")
     */
    public function showPadreEstructuraAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $padre = $em->createQueryBuilder()
            ->select('estructura', 'estructura_padre')
            ->from('EyCBundle:Estructura', 'estructura')
            ->join('estructura.estructurasPadres', 'estructura_padre')
            ->where("estructura.id = $id")
            ->getQuery();

        if ($padre != NULL) {
            $respuesta = array(
                'data' => $padre->getArrayResult(),
            );

            $response = new Response($this->serialize($respuesta));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;
        } else {
            return new Response('404 GET: La estructura solicitado con el identificador ' . $id . ' no tiene padre.');
        }
    }

    //****************************************Campos Estructura***********************************************

    /**
     * Devuelve los datos campos de una estructura
     * Responde al RF66 Listar campos de estructura
     * @Route("/{ide}/campos", name="eyc_estructura_campos", options={"expose"=true})
     * @Method("GET")
     */
    public function showCamposEstructuraAction($ide, Request $request)
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $order = $request->get('order', 'id');
        list($limit, $order, $direction) = $this->transformQuery($limit, $order);
        $em = $this->get('doctrine.orm.entity_manager');
        $camposestruc = $em->getRepository('EyCBundle:CampoEstruc')->createQueryBuilder('e')
            ->where('e.estructura =:id')
            ->setParameter('id', $ide)
            ->orderBy('e.' . $order, $direction)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        if (count($camposestruc) == 0 || $camposestruc == NULL) {
            return new Response('404 GET: La estructura solicitado con el identificador ' . $ide . ' no posee campos asociados.');
        } else {
            $paginator = new Paginator($camposestruc);
            $count = $paginator->count();

            $respuesta = array(
                'data' => $camposestruc->getArrayResult(),
                'count' => $count
            );
            $response = new Response($this->serialize($respuesta));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;
        }

    }

    /**
     * Devuelve los datos campos de una estructura
     * Responde al RF66 Listar campos de estructura
     * @Route("/{ide}/campos/sin_filtros", name="eyc_estructura_campos2", options={"expose"=true})
     * @Method("GET")
     */
    public function showCamposEstructura2Action($ide)
    {

        $em = $this->get('doctrine.orm.entity_manager');
        $camposestruc = $em->getRepository('EyCBundle:CampoEstruc')->createQueryBuilder('e')
            ->where('e.estructura =:id')
            ->setParameter('id', $ide)
            ->getQuery();

        if (count($camposestruc) == 0 || $camposestruc == NULL) {
            return new Response('404 GET: La estructura solicitado con el identificador ' . $ide . ' no posee campos asociados.');
        } else {
            $paginator = new Paginator($camposestruc);
            $count = $paginator->count();

            $respuesta = array(
                'data' => $camposestruc->getArrayResult(),
                'count' => $count
            );
            $response = new Response($this->serialize($respuesta));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;
        }

    }

    function eliminarCaracteresInvalidos($cadena)
    {
        $cadena = str_replace(" ", "", $cadena);
        $cadena = str_replace("ã¡", "a", $cadena);
        $cadena = str_replace("ã©", "e", $cadena);
        $cadena = str_replace("ã­", "i", $cadena);
        $cadena = str_replace("ã³", "o", $cadena);
        $cadena = str_replace("ãº", "u", $cadena);
        $cadena = str_replace("ã±", "n", $cadena);

        return $cadena;
    }

    /**
     * @Route("/{id}/campos_vinculados", name="eyc_estructura_campos_vinculados", options={"expose"=true})
     * @Method("GET")
     *
     */
    public function showCamposEstructuraVinculadoAction($id)
    {

        $em = $this->get('doctrine.orm.entity_manager');
        $camposnom = $em->getRepository('EyCBundle:CampoEstruc')->createQueryBuilder('e')
            ->where('e.estructura =:id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getArrayResult();

        if (count($camposnom) == 0 || $camposnom == NULL) {
            return new Response('404 GET: La estructura solicitado con el identificador ' . $id . ' no existe.');
        } else {

            $campoVinculados = array();

            for ($i = 0; $i < count($camposnom); $i++) {
                if ($camposnom[$i]['nomenclador'] != 0) {
                    $nombre = $this->eliminarCaracteresInvalidos(utf8_encode(strtolower($camposnom[$i]['nombre'])));
                    $campoVinculados[$nombre] = $this->get('nomenclador')->mostrarNomOp2($camposnom[$i]['nomenclador'])['valores'];
                }
            }

            $response = new Response($this->serialize($campoVinculados));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;
        }

    }


    /**
     * Adiciona un campo a una estructura
     * Responde al RF63 Adicionar campo a estructura
     * @Route("/{ide}/campo", name="eyc_estructura_campo_create", options={"expose"=true})
     * @Method("POST")
     */
    public function addCampoEstructuraAction($ide, Request $request)
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

        $em = $this->get('doctrine.orm.entity_manager');
        $existe = $em->getRepository('EyCBundle:CampoEstruc')->findOneBy(array('estructura' => $ide, 'nombre' => $nombre));

        if ($existe) {

            return new Response('404 POST: Existe un campo con el mismo nombre.');

        } else {

            $result = $this->get('estructura')->insertarCampoEstruc($ide, $nombre, $tipodato, $descripcion, $vinculado, $nomencladorvin);

            if ($result == 3) {
                return new Response('400 POST: El valor del tipo de dato es incorrecto.');
            }

            if ($result == 1) {
                return new Response('201 POST: La estructura se ha creado satisfactoriamente.');
            } else {
                return new Response('404 POST: La estructura solicitado con el identificador ' . $ide . ' no existe.');
            }
        }
    }

    /**
     * Actualiza los campos de una estructura
     * Responde al RF64 Modificar campo a estructura
     * @Route("/campo/{idc}", name="eyc_estructura_campo_update", options={"expose"=true})
     * @Method("PUT")
     *
     */
    public function updateCampoEstructuraAction($idc, Request $request)
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

        $resul = $this->get('estructura')->actualizarCampoEstruc($idc, $nombre, $tipodato, $descripcion, $vinculado, $nomencladorvin);

        if ($resul == 3) {

            return new Response('400 PUT: El valor del tipo de dato es incorrecto.');
        }

        if ($resul == 1) {

            return new Response('200 PUT: La estructura fue modificado satisfactoriamente.');
        } else
            return new Response('404 PUT: El campo de la estructura  con el identificador ' . $idc . ' no existe.');
    }

    /**
     * Elimina un  campo de una estructura
     * Responde al RF65 Eliminar campo a estructura
     * @Route("/campo/{id}", name="eyc_estructura_campo_delete", options={"expose"=true})
     * @Method("DELETE")
     */
    public function delCampoEstructuraAction($id)
    {
        $result = $this->get('estructura')->eliminarCampoEstruc($id);
        if ($result == 1) {

            return new Response('200 Delete: La estructura se eliminó con exito');

        } else {

            return new Response('404 Delete: El campo de la estructura  con el identificador ' . $id . ' no existe.');
        }
    }


//***************************************** EstructurasOP ********************************************

    /**
     * Mostrar una instancia de una estructura.
     * Responde al RF76 Listar instancias a estructura
     * int $id  El id de la instancia de la estructura que se quiere mostrar.
     * @Route("/est_op/{ide}", name="eyc_estructura_instancias", options={"expose"=true})
     * @Method("GET")
     */
    public function showAllEstructuraOpAction($ide, Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $camposnom = $em->getRepository('EyCBundle:CampoEstruc')->createQueryBuilder('e')
            ->where('e.estructura =:id')
            ->setParameter('id', $ide)
            ->getQuery();
        $paginator = new Paginator($camposnom);
        $count = $paginator->count();
        $cantCampos = $count;

        $filter = $request->get('filter', "");
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 5);
        $order = $request->get('order', 'id');
        list($limit, $order, $direction) = $this->transformQuery($limit, $order);

        $valores = $this->get('estructura')->mostrarValoresCamposEstrucOp($ide, $order, $page, $limit, $filter, $cantCampos, $direction);


        if ($valores != NULL) {
            $respuesta = $valores;

            if ($respuesta != NULL || count($respuesta)) {
                $response = new Response($this->serialize($respuesta));
                $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
                return $response;
            }
        } else {
            return new Response('404 GET: La estructura solicitado con el identificador ' . $ide . ' no posee instancias.');
        }

    }


    /**
     * Mostrar una instancia de una estructura.
     * Responde al RF76 Listar instancias a estructura
     * int $id  El id de la instancia de la estructura que se quiere mostrar.
     * @Route("/est_op/{ide}/sin_filtro", name="eyc_estructura_instancias2", options={"expose"=true})
     * @Method("GET")
     */
    public function showAllEstructuraOp2Action($ide)
    {

        $valores = $this->get('estructura')->mostrarValoresCamposEstrucOp2($ide);

        if ($valores != NULL) {
            $respuesta = $valores;

            if ($respuesta != NULL || count($respuesta)) {
                $response = new Response($this->serialize($respuesta));
                $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
                return $response;
            }
        } else {
            return new Response('404 GET: La estructura solicitado con el identificador ' . $ide . ' no posee instancias.');
        }

    }

    /**
     * Buscar estructuraOP dado el nombre
     * Responde al RF75 Buscar instancia de Estructura
     * @Route("/buscar_estructuraop/", name="eyc_estructura_buscar_instancia", options={"expose"=true})
     * @Method("POST")
     */
    public function buscarEstructuraOp(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $criterio = $request->request->get('criterio');
        $ide = $request->request->get('ide');

        $idEstrucOPS = $this->get('estructura')->mostrarNomeOpsByEstructura($ide);// ids, y nombre de las estructuras op

        $result = array();

        if (count($idEstrucOPS) > 0) {

            $respuesta = array();
            $campos = array();
            $valores = array();
            foreach ($idEstrucOPS as $id) {
                array_push($campos, $id['nomCampo']);
                array_push($valores, $id['valor']);
            }
            $respuesta['IdEop'] = $ide;
            $respuesta['NomEOP'] = $idEstrucOPS[0]['NomEOP'];
            $respuesta['campos'] = $campos;
            $respuesta['valores'] = $valores;

            $response = new Response($this->serialize($respuesta));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;

        } else {
            $response = new Response($this->serialize($result));
            $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
            return $response;
        }
    }

    /**
     * Adiciona una  estructura operatoria
     * Responde al RF72 Adicionar instancia a estructura
     * @Route("/op/{ide}", name="eyc_estructura_instancia_create", options={"expose"=true})
     * @Method("POST")
     */
    public function addEstructuraOpAction($ide, Request $request)
    {
        $nombre = $request->request->get('nombre');
        $idep = $request->request->get('idop');
        $valores = $request->request->getIterator('valores');

        $result = $this->get('estructura')->insertarEstrucOp($ide, $nombre, $valores['valores'], $idep);

        switch ($result) {
            case 1:
                return new Response('201 POST: La estructura se ha creado satisfactoriamente.');
                break;
            case 2:
                return new Response('400 POST: El valor del tipo de dato es incorrecto.');
                break;
            case 3:
                return new Response('400 POST: La estructura no es subordinada.');
                break;
            case 4:
                return new Response('400 POST: Los valores insertados no coinciden con los campos de la estructura.');
                break;
            case 5:
                return new Response('400 POST: El arreglo de valores no coincide con los campos de la estructura.');
                break;
            case 6:
                return new Response('400 POST: El arreglo de valores no tiene la estructura esperada.');
                break;
            case 7:
                return new Response('400 POST: La estructura de la que se quiere insertar una instancia no existe o no posee campos.');
                break;
        }

    }

    /**
     * Modifica una  estructura operatoria
     * Responde al RF73 Modificar instancia a estructura
     * @Route("/op/{ideop}", name="eyc_estructura_instancia_update", options={"expose"=true})
     * @Method("PUT")
     */
    public function editEstructuraOpAction($ideop, Request $request)
    {
        $nombre = $request->request->get('nombre');
        $idp = $request->request->get('idp');
        $valores = $request->request->getIterator('valores');

        $result = $this->get('estructura')->actualizarEstrucOp($ideop, $idp, $nombre, $valores['valores']);

        switch ($result) {
            case 1:
                return new Response('200 PUT: La estructura fue modificado satisfactoriamente.');
                break;
            case 2:
                return new Response('400 PUT: El arreglo de valores no coincide con los campos de la estructura.');
                break;
            case 3:
                return new Response('400 PUT: El arreglo de valores no coincide con los campos del estructura.');
                break;
            case 4:
                return new Response('400 PUT: El arreglo de valores no tiene la estructura esperada.');
                break;
            case 5:
                return new Response('400 PUT: No se puede obtener campos para la instancia de la estructura especificada.');
                break;
            case 6:
                return new Response('404 PUT: No se puede obtener una instancia de la estructura especificado.');
                break;
        }
    }

    /**
     * Elimina una estructura operatoria
     * Responde al RF74 Eliminar instancia a estructura
     * integer id El id de la instancia quiere se eliminar.
     * @Route("/op/{id}", name="eyc_estructura_instancia_delete", options={"expose"=true})
     * @Method("DELETE")
     */
    public function delEstructuraOpAction($id)
    {
        $result = $this->get('estructura')->eliminarEstrucOp($id);
        if ($result == 1) {
            return new Response('200 Delete: La estructura se eliminó con exito.');
        } else

            return new Response('404 Delete: El campo de la estructura  con el identificador ' . $id . ' no existe.');

    }

    /**
     * Mostrar arbol de estructura operatoria
     * Responde al RFno Mostrar arbol de estructura operatoria
     * @Route("/arbol_estructura_op/", name="eyc_estructura_arbol_estructura_op", options={"expose"=true})
     * @Method("GET")
     */
    public function arbolEstructuraOpAction()
    {

        $arbolEOP = $this->get('estructura')->mostrarArbolEstrucOps();

        $respuesta = array(
            'data' => $arbolEOP
        );
        $response = new Response($this->serialize($respuesta));
        $response->headers->add(array('Access-Control-Allow-Origin' => '*'));
        return $response;

    }

}
