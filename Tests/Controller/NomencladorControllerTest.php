<?php

namespace UCI\Boson\EyCBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NomencladorControllerTest extends WebTestCase
{
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->nomenclador = static::$kernel->getContainer() ->get('nomenclador');
    }

    //****************************************Estas funciones gestionan los nomencladores********************


    /**
     * Devuelve todas los Nomencladores
     * Responde al RF51 Listar nomencladores
     */

    public function testMostrarNomencladoresIndex()
    {
        $client = static::createClient();

        $nomservice = $client->request('GET', '/nomenclador/');

        $this->assertNotEmpty($nomservice->text());

    }


    /**
     * Crea un Nomenclador
     * Responde al RF47 Adicionar nomenclador
     */

    public function testIntertarNomenclador()
    {
        $client = static::createClient();

        $nomservice =  $client->request('POST', '/nomenclador/', array('nombre' => 'UBE'));
        $nomservice400 =  $client->request('POST', '/nomenclador/', array('anombasre' => 'Fabien'));

        $this->assertStringStartsWith('201',$nomservice->text());
        $this->assertStringStartsWith('400',$nomservice400->text());
    }


    /**
     * Devuelve un nomenclador dado un id
     * Responde al RF Obtener un nomenclador
     */

    public function testMostrarNomencladorIndex()
    {
        $client = static::createClient();

        $mostrarnoms = $this->nomenclador->mostrarNoms();
        $idn = array_pop($mostrarnoms)->getId();

        $nomservice404 = $client->request('GET', '/nomenclador/asd');
        $nomservice = $client->request('GET', '/nomenclador/'.$idn.'');

        $this->assertStringStartsWith('404',$nomservice404->text());
        $this->assertNotEmpty($nomservice->text());

    }


        /**
         * Edita un Nomenclador
         * Responde al RF48 Modificar nomenclador
         */

    public function testActualizarNomenclador()
    {
        $client = static::createClient();

        $mostrarnoms = $this->nomenclador->mostrarNoms();
        $idn = array_pop($mostrarnoms)->getId();

        $nomservice =  $client->request('POST', '/nomenclador/'.$idn.'', array('_method'=>'PUT','nombre' => 'UBE Modificado'));
        $nomservice404 =  $client->request('POST', '/nomenclador/99', array('_method'=>'PUT','nombre' => 'Dannel mod'));
        $nomservice400 =  $client->request('POST', '/nomenclador/'.$idn.'', array('_method'=>'PUT','nodfmbre' => 'Dannel mod'));

        $this->assertStringStartsWith('200',$nomservice->text());
        $this->assertStringStartsWith('404',$nomservice404->text());
        $this->assertStringStartsWith('400',$nomservice400->text());
    }


        /**
         * Buscar nomenclador dado el nombre
         * Responde al RF50 Buscar nomenclador
         * @Route("/buscar_nomenclador/")
         * @Method("POST")
         */

        public function testBuscarNomenclador()
    {
        $client = static::createClient();

        $busca= $client->request('POST', '/nomenclador/buscar_nomenclador/', array('criterio' => 'U'));
        $buscaNot= $client->request('POST', '/nomenclador/buscar_nomenclador/', array('criterio' => 'XXXXXX'));

        $this->assertNotEmpty($busca);
        $this->assertNotEmpty($buscaNot);
    }



//    //****************************************Campos Nomenclador***************************************************************


        /**
         * Adiciona un campo a un Nomenclador
         * Responde al RF59	Adicionar campo
         */

    public function testAddCampoNomenclador()
    {
        $client = static::createClient();

        $mostrarnoms = $this->nomenclador->mostrarNoms();
        $idn = array_pop($mostrarnoms)->getId();

        $nomservice =  $client->request('POST', '/nomenclador/'.$idn.'/campo', array('nombre' => 'Nombre','tipodato'=>'string','descripcion'=>'Nombre de la Asignatura','vinculado'=>false,'nomencladorvin'=>0));
        $nomservice404 =  $client->request('POST', '/nomenclador/99/campo', array('nombre' => 'Nombre','tipodato'=>'string','descripcion'=>'Nombre de la Asignatura','vinculado'=>false,'nomencladorvin'=>0));
        $nomservice400 =  $client->request('POST', '/nomenclador/'.$idn.'/campo', array('nombre' => 'Nombre','tipodato'=>'strsding','descripcion'=>'Nombre de la Asignatura','vinculado'=>false,'nomencladorvin'=>0));

        $this->assertStringStartsWith('201',$nomservice->text());
        $this->assertStringStartsWith('404',$nomservice404->text());
        $this->assertStringStartsWith('400',$nomservice400->text());
    }

    /**
     * Devuelve el campo dado el id
     * Responde al RF62 Listar campos de nomenclador
     *
     */

    public function testShowCamposNomenclador()
    {
        $client = static::createClient();

        $mostrarnoms = $this->nomenclador->mostrarNoms();
        $idn = array_pop($mostrarnoms)->getId();

        $campos_nom= $client->request('GET', '/nomenclador/campo_nom/'.$idn.'');
        $campos_nomNot= $client->request('GET', '/nomenclador/campo_nom/999999');

        $this->assertNotEmpty($campos_nom);
        $this->assertNotEmpty($campos_nomNot);
    }




    /**
     * Actualiza los campos de un nomenclador
     * Responde al RF60	Modificar campo
     *
     */

    public function testActualizarCampoNomenclador()
    {
        $client = static::createClient();

        $mostrarnoms = $this->nomenclador->mostrarNoms();
        $idn = array_pop($mostrarnoms)->getId();

        $camposnom = $this->nomenclador->mostrarCamposNom($idn);
        $idcn = array_pop($camposnom)->getId();

        $nomservice =  $client->request('POST', '/nomenclador/campo_nom/'.$idcn.'', array('_method'=>'PUT','nombre' => 'NombreC','tipodato'=>'string','descripcion'=>'descripcion Idalmis','vinculado'=>false,'nomencladorvin'=>0));
        $nomservice404 =  $client->request('POST', '/nomenclador/campo_nom/99', array('_method'=>'PUT','nombre' => 'NombreC','tipodato'=>'string','descripcion'=>'descripcion Idalmis','vinculado'=>false,'nomencladorvin'=>0));
        $nomservice400 =  $client->request('POST', '/nomenclador/campo_nom/'.$idcn.'', array('_method'=>'PUT','nombre' => 'NombreC','tipodato'=>'strisdng','descripcion'=>'descripcion Idalmis','vinculado'=>false,'nomencladorvin'=>0));

        $this->assertStringStartsWith('200',$nomservice->text());
        $this->assertStringStartsWith('404',$nomservice404->text());
        $this->assertStringStartsWith('400',$nomservice400->text());
    }




//************************************************* NomencladorOP *************************************************************


    /**
     * Adiciona una instancia de un Nomenclador
     * Responde al RF67 Adicionar instancia a nomenclador
     */

    public function testAddNomencladorOp()
    {
        $client = static::createClient();

        $mostrarnoms = $this->nomenclador->mostrarNoms();
        $idn = array_pop($mostrarnoms)->getId();

        $camposnom = $this->nomenclador->mostrarCamposNom($idn);
        $idcn = array_pop($camposnom)->getId();

        $nomservice =  $client->request('POST', '/nomenclador/nom_op/'.$idn.'' ,array('camposnom'=>'UBE55', 'valores'=> array(array('id' => $idcn, 'valor' => 'Maximo Gomez'))));
        $nomservice5 =  $client->request('POST', '/nomenclador/nom_op/99',array('camposnom'=>'UBE55', 'valores'=> array(array('id' => $idcn, 'valor' => 'Maximo Gomez'))));
        $nomservice2 =  $client->request('POST', '/nomenclador/nom_op/'.$idn.'' ,array('camposnom'=>'UBE55', 'valores'=> array(array('id' => 99, 'valor' => 'Maximo Gomez'))));
        $nomservice4 =  $client->request('POST', '/nomenclador/nom_op/'.$idn.'' ,array('camposnom'=>'UBE55', 'valores'=> array()));

        $this->assertStringStartsWith('201',$nomservice->text());
        $this->assertStringStartsWith('400',$nomservice5->text());
        $this->assertStringStartsWith('400',$nomservice2->text());
        $this->assertStringStartsWith('400',$nomservice4->text());
    }


    /**
     * Mostrar una instancia de un nomenclador.
     * Responde al RF71 Listar instancias a nomenclador
     */

    public function testShowAllNomencladorOP()
    {
        $client = static::createClient();

        $mostrarnoms = $this->nomenclador->mostrarNoms();
        $idn = array_pop($mostrarnoms)->getId();

        $get= $client->request('GET', '/nomenclador/nom_op_all/'.$idn.'');
        $getNot= $client->request('GET', '/nomenclador/nom_op_all/999999');

        $this->assertNotEmpty($get);
        $this->assertNotEmpty($getNot);
    }

    /**
     * Mostrar todas las instancias de un nomenclador.
     * Responde al RF Mostrar una instancia de un nomenclador.
     */
    public function testShowNomencladorOp()
    {
        $client = static::createClient();

        $mostrarnoms = $this->nomenclador->mostrarNoms();
        $idn = array_pop($mostrarnoms)->getId();

        $nomops = $this->nomenclador->mostrarNomOps($idn);
        $idnop = array_pop($nomops)->getId();

        $get= $client->request('GET', '/nomenclador/nom_op/'.$idnop.'');
        $getNot= $client->request('GET', '/nomenclador/nom_op/9999999');

        $this->assertNotEmpty($get);
        $this->assertNotEmpty($getNot);


    }

    /**
     * Buscar nomencladorOP dado el nombre
     * Responde al RF50 Buscar nomenclador
     */
    public function testBuscarNomencladorOP()
    {
        $client = static::createClient();

        $mostrarnoms = $this->nomenclador->mostrarNoms();
        $idn = array_pop($mostrarnoms)->getId();

        $busca= $client->request('POST', '/nomenclador/buscar_nomencladorop/', array('idn'=>$idn,'criterio' => 'U'));
        $buscaNot= $client->request('POST', '/nomenclador/buscar_nomencladorop/', array('idn'=>$idn, 'criterio' => 'm'));

        $this->assertNotEmpty($busca);
        $this->assertNotEmpty($buscaNot);
    }


    /**
     * Modifica una instancia de un Nomenclador
     * Responde al RF68 Modificar instancia a nomenclador
     */

    public function testActualizarNomencladorOp()
    {
        $client = static::createClient();

        $mostrarnoms = $this->nomenclador->mostrarNoms();
        $idn = array_pop($mostrarnoms)->getId();

        $camposnom = $this->nomenclador->mostrarCamposNom($idn);
        $idcn = array_pop($camposnom)->getId();

        $nomops = $this->nomenclador->mostrarNomOps($idn);
        $idnop = array_pop($nomops)->getId();


        $nomservice =  $client->request('POST', '/nomenclador/nom_op/'.$idnop.'',array('_method'=>'PUT','camposnom'=>'EntidadOP', 'valores'=>array(array('id' => $idcn,'valor'=>'modificado1'))));
        $nomservice5 =  $client->request('POST', '/nomenclador/nom_op/1',array('_method'=>'PUT','camposnom'=>'JIM Moderder', 'valores'=>array(array('id' => $idcn,'valor'=>'modificado1'))));
        $nomservice6 =  $client->request('POST', '/nomenclador/nom_op/99',array('_method'=>'PUT','camposnom'=>'JIM Moderder', 'valores'=>array(array('id' => $idcn,'valor'=>'modificado1'))));
        $nomservice2 =  $client->request('POST', '/nomenclador/nom_op/'.$idnop.'',array('_method'=>'PUT','camposnom'=>'JIM Moderder', 'valores'=>array(array('id' => 99,'valor'=>'modificado1'))));
        $nomservice3 =  $client->request('POST', '/nomenclador/nom_op/'.$idnop.'',array('_method'=>'PUT','camposnom'=>'JIM Moderder', 'valores'=>array()));

        $this->assertStringStartsWith('200',$nomservice->text());
        $this->assertStringStartsWith('404',$nomservice6->text());
        $this->assertStringStartsWith('400',$nomservice5->text());
        $this->assertStringStartsWith('400',$nomservice2->text());
        $this->assertStringStartsWith('400',$nomservice3->text());

    }

    /**
     * Adiciona una instancia de un Nomenclador
     * Responde al RF69 Eliminar instancia a nomenclador
     */

    public function testDeleteNomencladorOp()
    {
        $client = static::createClient();

        $mostrarnoms = $this->nomenclador->mostrarNoms();
        $idn = array_pop($mostrarnoms)->getId();

        $nomops = $this->nomenclador->mostrarNomOps($idn);
        $idnop = array_pop($nomops)->getId();

        $nomservice =  $client->request('DELETE', '/nomenclador/nom_op/'.$idnop.'');
        $nomservice404 =  $client->request('DELETE', '/nomenclador/nom_op/999999');

        $this->assertStringStartsWith('200',$nomservice->text());
        $this->assertStringStartsWith('404',$nomservice404->text());
    }


    /**
     * Elimina un  campo de un nomenclador
     * Responde al RF61	Eliminar campo
     */

    public function testEliminarCampoNomenclador()
    {
        $client = static::createClient();

        $mostrarnoms = $this->nomenclador->mostrarNoms();
        $idn = array_pop($mostrarnoms)->getId();

        $camposnom = $this->nomenclador->mostrarCamposNom($idn);
        $idcn = array_pop($camposnom)->getId();

        $nomservice =  $client->request('DELETE', '/nomenclador/campo_nom/'.$idcn.'');
        $nomservice404 =  $client->request('DELETE', '/nomenclador/campo_nom/99');

        $this->assertStringStartsWith('200',$nomservice->text());
        $this->assertStringStartsWith('404',$nomservice404->text());
    }


    /**
     * Elimina un Nomenclador
     * Responde al RF49 Eliminar nomenclador
     */

    public function testEliminarNomenclador()
    {
        $client = static::createClient();

        $mostrarnoms = $this->nomenclador->mostrarNoms();
        $idn = array_pop($mostrarnoms)->getId();

        $nomservice =  $client->request('DELETE', '/nomenclador/'.$idn.'');
        $nomservice404 =  $client->request('DELETE', '/nomenclador/99999');

        $this->assertStringStartsWith('200',$nomservice->text());
        $this->assertStringStartsWith('404',$nomservice404->text());
    }

}
