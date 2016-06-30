<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 14/05/15
 * Time: 14:29
 */

namespace UCI\Boson\EyCBundle\Tests\Service;



use UCI\Boson\EyCBundle\Entity\CampoEstruc;
use UCI\Boson\EyCBundle\Entity\Estructura;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use UCI\Boson\EyCBundle\Entity\EstructuraOp;
use UCI\Boson\EyCBundle\Entity\ValoresEstruc;


class ServiciosEstructuraTest extends WebTestCase
 {

    private $estructura;


    public function __construct()
    {
        parent::__construct();
    }

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->estructura = static::$kernel->getContainer() ->get('estructura');
    }

//////////////////////////////////////////////// // Estas funciones prueban las estructuras////////////////////

    //prueba la inserción de una estructura
    public function testInsertarEstruc()
    {
        $insertar = $this->estructura->insertarEstruc('prueba', true);

        $this->assertTrue( is_integer($insertar));
    }

    //prueba la modificación de una estructura
    public function testActualizarEstruc()
    {
        $mostrarestrucs = $this->estructura->mostrarEstrucs();

        $actualizar = $this->estructura->actualizarEstruc(array_pop($mostrarestrucs)->getId(), 'actualizar', true);

        $this->assertTrue( $actualizar);
    }

    //prueba mostrar estructuras
    public function testMostrarEstrucs()
    {
        $mostrarestrucs = $this->estructura->mostrarEstrucs();

        $this->assertTrue( array_pop($mostrarestrucs) instanceof Estructura);
    }

    //prueba mostrar una estructura
    public function testMostrarEstruc()
    {
        $mostrarestrucs = $this->estructura->mostrarEstrucs();

        $mostrarestrucid = $this->estructura->mostrarEstruc(array_pop($mostrarestrucs)->getId());

        $this->assertTrue( $mostrarestrucid instanceof Estructura);
    }

    /*********************************** termina la prueba de las estructuras **********************************/



////////////////////////////////////// Estas funciones prueban los  campos de las estructuras/////////////

    //prueba la inserción de un campo de una estructura
    public function testInsertarCampoEstruc()
    {
        $mostrarestrucs = $this->estructura->mostrarEstrucs();

        $insercion = $this->estructura->insertarCampoEstruc(array_pop($mostrarestrucs)->getId() , 'campo', 'string', 'descrpcion del campo', false);

        $this->assertTrue( $insercion);
    }


    //prueba la modificación de un campo de una estructura
    public function testActualizarCampoEstruc()
    {
        $mostrarestrucs = $this->estructura->mostrarEstrucs();

        $camposestruc = $this->estructura->mostrarCamposEstruc(array_pop($mostrarestrucs)->getId());

        $actualizar = $this->estructura->actualizarCampoEstruc(array_pop($camposestruc)->getId() , 'campo', 'string', 'descrpcion del campo', false, 0);

        $this->assertTrue( $actualizar);
    }

    //prueba mostrar un campo de una estructura
    public function testMostrarCampoEstruc()
    {
        $mostrarestrucs = $this->estructura->mostrarEstrucs();

        $camposestruc = $this->estructura->mostrarCamposEstruc(array_pop($mostrarestrucs)->getId());

        $mostrarcampoestruc = $this->estructura->mostrarCampoEstruc(array_pop($camposestruc)->getId());

        $this->assertTrue( $mostrarcampoestruc instanceof CampoEstruc);
    }

    /*********************************** termina la prueba de los campos de una estructura **********************************/



///////////////////////////////////////// Estas funciones prueban las  estructuraOP/////////////////////////////

    //prueba insertar  una estructuraOp
    public function testInsertarEstrucOp()
    {
        $mostrarestrucs = $this->estructura->mostrarEstrucs();
        $idestruc = array_pop($mostrarestrucs)->getId();


        $camposestruc = $this->estructura->mostrarCamposEstruc($idestruc);

        $insertarestrucop = $this->estructura
            ->insertarEstrucOp($idestruc, 'EstrucOp', array(array('id' => array_pop($camposestruc)->getId(), 'valor' => 'EstrucOp')));

        $this->assertTrue($insertarestrucop);
    }

    //prueba mostrar  estructuraOp de una estructura
    public function testMostrarEstrucOps()
    {
        $mostrarestrucs = $this->estructura->mostrarEstrucs();
        $idestruc = array_pop($mostrarestrucs)->getId();

        $camposestruc = $this->estructura->mostrarEstrucOps($idestruc);

        $this->assertTrue(array_pop($camposestruc) instanceof EstructuraOp);
    }

    //prueba mostrar  EstructuraOp
    public function testMostrarEstrucOp()
    {
        $mostrarestrucs = $this->estructura->mostrarEstrucs();

        $estrucops = $this->estructura->mostrarEstrucOps(array_pop($mostrarestrucs)->getId());

        $estrucop = $this->estructura->mostrarEstrucOp(array_pop($estrucops)->getId());

        $this->assertTrue($estrucop instanceof EstructuraOp);
    }

    //prueba mostrar valores de los Campos de una EstructuraOp
    public function testMostrarValoresCamposEstrucOp()
    {
        $mostrarestrucs = $this->estructura->mostrarEstrucs();

        $estrucops = $this->estructura->mostrarEstrucOps(array_pop($mostrarestrucs)->getId());

        $valoresestrucop = $this->estructura->mostrarValoresCamposEstrucOp(array_pop($estrucops)->getId());

        $this->assertTrue($valoresestrucop['estruc'] instanceof EstructuraOp);
    }

    //prueba actualizar  un EstructuraOp
    public function testActualizarEstrucOp()
    {
        $mostrarestrucs = $this->estructura->mostrarEstrucs();
        $idestruc = array_pop($mostrarestrucs)->getId();


        $camposestruc = $this->estructura->mostrarCamposEstruc($idestruc);


        $estrucops = $this->estructura->mostrarEstrucOps($idestruc);


        $actualizarestrucop = $this->estructura
            ->actualizarEstrucOp(array_pop($estrucops)->getId(), 'EstrucOp', array(array('id' => array_pop($camposestruc)->getId(), 'valor' => 'EstrucOpAct')));

        $this->assertTrue($actualizarestrucop);
    }

    /*********************************** termina la prueba de las estructurasOp **********************************/


///////////////////////////////ESTAS SON LAS PRUEBAS DE LOS ELIMINAR //////////////////////////////////////

    //prueba eliminar una estructuraOp
    public function testEliminarEstrucOp()
    {
        $mostrarestrucs = $this->estructura->mostrarEstrucs();

        $estrucop = $this->estructura->mostrarEstrucOps(array_pop($mostrarestrucs)->getId());

        $eliminarestrucop = $this->estructura->eliminarEstrucOp(array_pop($estrucop)->getId());

        $this->assertTrue($eliminarestrucop);
    }



    //prueba eliminar un campo de una estructura
    public function testEliminarCampoEstruc()
    {
        $mostrarestrucs = $this->estructura->mostrarEstrucs();

        $camposestruc = $this->estructura->mostrarCamposEstruc(array_pop($mostrarestrucs)->getId());

        $eliminarcampoestruc = $this->estructura->eliminarCampoEstruc(array_pop($camposestruc)->getId());

        $this->assertTrue($eliminarcampoestruc);
    }


    //prueba la eliminación de una estructura
    public function testEliminarEstruc()
    {
        $mostrarestrucs = $this->estructura->mostrarEstrucs();

        $eliminar = $this->estructura->eliminarEstruc(array_pop($mostrarestrucs)->getId());

        $this->assertTrue($eliminar);
    }


    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
 
