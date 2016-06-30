<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 14/05/15
 * Time: 14:29
 */

namespace UCI\Boson\EyCBundle\Tests\Service;



use UCI\Boson\EyCBundle\Entity\CampoNom;
use UCI\Boson\EyCBundle\Entity\Nomenclador;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use UCI\Boson\EyCBundle\Entity\NomencladorOp;
use UCI\Boson\EyCBundle\Entity\ValoresNom;


class ServiciosNomencladorTest extends WebTestCase
 {

    private $nomenclador;


    public function __construct()
    {
        parent::__construct();
    }

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->nomenclador = static::$kernel->getContainer() ->get('nomenclador');
    }

//////////////////////////////////////////////// // Estas funciones prueban los nomencladores////////////////////

    //prueba la inserción de un nomenclador
    public function testInsertarNom()
    {
        $insertar = $this->nomenclador->insertarNom('prueba');

        $this->assertTrue( $insertar);
    }

    //prueba la modificación de un nomenclador
    public function testActualizarNom()
    {
        $mostrarnoms = $this->nomenclador->mostrarNoms();

        $actualizar = $this->nomenclador->actualizarNom(array_pop($mostrarnoms)->getId() , 'actualizar');

        $this->assertTrue( $actualizar);
    }

    //prueba mostrar nomencladores
    public function testMostrarNoms()
    {
        $mostrarnoms = $this->nomenclador->mostrarNoms();

        $this->assertTrue( array_pop($mostrarnoms) instanceof Nomenclador);
    }

    //prueba mostrar un nomenclador
    public function testMostrarNom()
    {
        $mostrarnoms = $this->nomenclador->mostrarNoms();

        $mostrarnomid = $this->nomenclador->mostrarNom(array_pop($mostrarnoms)->getId());

        $this->assertTrue( $mostrarnomid instanceof Nomenclador);
    }

    /*********************************** termina la prueba de los nomenladores **********************************/



////////////////////////////////////// Estas funciones prueban los  campos de los nomencladores/////////////

    //prueba la inserción de un campo de un nomenclador
    public function testInsertarCampoNom()
    {
        $mostrarnoms = $this->nomenclador->mostrarNoms();

        $insercion = $this->nomenclador->insertarCampoNom(array_pop($mostrarnoms)->getId() , 'campo', 'string', 'descrpcion del campo', false);

        $this->assertTrue( $insercion);
    }


    //prueba la modificación de un campo de un nomenclador
    public function testActualizarCampoNom()
    {
        $mostrarnoms = $this->nomenclador->mostrarNoms();

        $camposnom = $this->nomenclador->mostrarCamposNom(array_pop($mostrarnoms)->getId());

        $actualizar = $this->nomenclador->actualizarCampoNom(array_pop($camposnom)->getId() , 'campo', 'string', 'descrpcion del campo', false, 0);

        $this->assertTrue( $actualizar);
    }

    //prueba mostrar un campo de un nomenclador
    public function testMostrarCampoNom()
    {
        $mostrarnoms = $this->nomenclador->mostrarNoms();

        $camposnom = $this->nomenclador->mostrarCamposNom(array_pop($mostrarnoms)->getId());

        $mostrarcamponom = $this->nomenclador->mostrarCampoNom(array_pop($camposnom)->getId());

        $this->assertTrue( $mostrarcamponom instanceof CampoNom);
    }

    /*********************************** termina la prueba de los campos de nomenladores **********************************/



///////////////////////////////////////// Estas funciones prueban los  nomencladorOP/////////////////////////////

    //prueba insertar  un nomencladorOp
    public function testInsertarNomOp()
    {
        $mostrarnoms = $this->nomenclador->mostrarNoms();
        $idnom = array_pop($mostrarnoms)->getId();


        $camposnom = $this->nomenclador->mostrarCamposNom($idnom);

        $insertarnomop = $this->nomenclador
            ->insertarNomOp($idnom, 'NomOp', array(array('id' => array_pop($camposnom)->getId(), 'valor' => 'NomOp')));

        $this->assertTrue($insertarnomop);
    }

    //prueba mostrar  nomencladorOp de un nomenclador
    public function testMostrarNomOps()
    {
        $mostrarnoms = $this->nomenclador->mostrarNoms();
        $idnom = array_pop($mostrarnoms)->getId();

        $camposnom = $this->nomenclador->mostrarNomOps($idnom);

        $this->assertTrue(array_pop($camposnom) instanceof NomencladorOp);
    }

    //prueba mostrar  nomencladorOp
    public function testMostrarNomOp()
    {
        $mostrarnoms = $this->nomenclador->mostrarNoms();

        $nomops = $this->nomenclador->mostrarNomOps(array_pop($mostrarnoms)->getId());

        $nomop = $this->nomenclador->mostrarNomOp(array_pop($nomops)->getId());

        $this->assertTrue($nomop instanceof NomencladorOp);
    }

    //prueba mostrar valores de los Campos de un nomencladorOp
    public function testMostrarValoresCamposNomOp()
    {
        $mostrarnoms = $this->nomenclador->mostrarNoms();

        $nomops = $this->nomenclador->mostrarNomOps(array_pop($mostrarnoms)->getId());

        $valoresnomop = $this->nomenclador->mostrarValoresCamposNomOp(array_pop($nomops)->getId());

        $this->assertTrue($valoresnomop['nom'] instanceof NomencladorOp);
    }

    //prueba actualizar  un nomencladorOp
    public function testActualizarNomOp()
    {
        $mostrarnoms = $this->nomenclador->mostrarNoms();
        $idnom = array_pop($mostrarnoms)->getId();


        $camposnom = $this->nomenclador->mostrarCamposNom($idnom);


        $nomops = $this->nomenclador->mostrarNomOps($idnom);


        $actualizarnomop = $this->nomenclador
            ->actualizarNomOp(array_pop($nomops)->getId(), 'NomOp', array(array('id' => array_pop($camposnom)->getId(), 'valor' => 'NomOpAct')));

        $this->assertTrue($actualizarnomop);
    }

    /*********************************** termina la prueba de los nomenladoresOp **********************************/


///////////////////////////////ESTAS SON LAS PRUEBAS DE LOS ELIMINAR //////////////////////////////////////

    //prueba eliminar un nomencladorOp
    public function testEliminarNomOp()
    {
        $mostrarnoms = $this->nomenclador->mostrarNoms();

        $nomop = $this->nomenclador->mostrarNomOps(array_pop($mostrarnoms)->getId());

        $eliminarnomop = $this->nomenclador->eliminarNomOp(array_pop($nomop)->getId());

        $this->assertTrue($eliminarnomop);
    }



    //prueba eliminar un campo de un nomenclador
    public function testEliminarCampoNom()
    {
        $mostrarnoms = $this->nomenclador->mostrarNoms();

        $camposnom = $this->nomenclador->mostrarCamposNom(array_pop($mostrarnoms)->getId());

        $eliminarcamponom = $this->nomenclador->eliminarCampoNom(array_pop($camposnom)->getId());

        $this->assertTrue($eliminarcamponom);
    }


    //prueba la eliminación de un nomenclador
    public function testEliminarNom()
    {
        $mostrarnoms = $this->nomenclador->mostrarNoms();

        $eliminar = $this->nomenclador->eliminarNom(array_pop($mostrarnoms)->getId());

        $this->assertTrue( $eliminar);
    }


    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
 
