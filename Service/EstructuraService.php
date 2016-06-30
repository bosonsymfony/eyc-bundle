<?php

namespace UCI\Boson\EyCBundle\Service;

use UCI\Boson\EyCBundle\Entity\CampoEstruc;
use UCI\Boson\EyCBundle\Entity\ValoresEstruc;
use UCI\Boson\EyCBundle\Entity\Estructura;
use UCI\Boson\EyCBundle\Entity\EstructuraOp;
use Symfony\Component\Validator\Constraints\Date;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Symfony\Component\Validator\ValidatorInterface as Validador;


class EstructuraService
{

    private $doctrine;
    private $validador;

    /**
     * Constructor de la clase.
     *
     * @param Doctrine $doctrine Contenedor de doctrine.
     * @param Validador $validador Contenedor validator.
     *
     */
    public function __construct( Doctrine $doctrine, Validador $validador)
    {
        $this->doctrine = $doctrine;
        $this->validador = $validador;
    }

    /**
     * Verifica que valor es del tipo indicado.
     *
     * @param string $tipo Un tipo de dato valido.
     * @param string $valor Un valor dado.
     *
     * @return bool Verdadero si valor es del tipo especificado, false en otro caso.
     *
     */
    public function validarTipoValor($tipo, $valor)
    {
        $tipos = array("bool", "integer", "double", "string", "date");

        if (is_string($tipo) && is_string($valor))
        {
            $i = array_search($tipo, $tipos);

            switch ($i)
            {
                case 0:
                    return (is_bool($valor) || is_bool(boolval($valor))) ? true : false;
                case 1:
                    return (is_integer($valor) || is_integer(intval($valor))) ? true : false;
                case 2:
                    return (is_double($valor) || is_double(doubleval($valor))) ? true : false;
                case 3:

                    return (is_string($valor)) ? true : false;
                case 4:
                    $dateConstraint = new Date();
                    $errorList = $this->validador->validateValue($valor, $dateConstraint);
                    return (count($errorList) == 0) ? true : false;

            }
        }
        return false;
    }



    /**
     * Convierte un  valor al tipo indicado.
     *
     * @param string $tipo Un tipo de dato valido.
     * @param string $valor Un valor dado.
     *
     * @return valor Convertido al tipo especificado, null en otro caso.
     *
     */

    public function convertirValorTipo($tipo, $valor)
    {
        $tipos = array("bool", "integer", "double", "string", "date");

        if (is_string($tipo) && is_string($valor))
        {
            $i = array_search($tipo, $tipos);
            $i == 4 ? settype($valor, $tipos[3]) : settype($valor, $tipos[$i]);

            return $valor;
        }
        return NULL;
    }


//////////////////////////////////////////////// // Estas funciones gestionan las estructuras////////////////////


    /**
     * Inserta una estructura
     *
     * @param string $nombre El nombre de la estructura
     * @param bool $raiz Si puede ocupar la posicion inicial del arbol jerarquico
     *
     * @return  ConstraintViolationListInterface o integer El id de la estructura si se pudo insertar correctamente,
     *          en otro caso retorna ConstraintViolationListInterface una lista de violaciones de restricciones
     *          a la hora de insertar la estructura
     *
     * Responde al RF (67)(Adicionar estructura)
     *
     */
    public function insertarEstruc($nombre, $raiz)
    {
        $estructura = new Estructura();
        $estructura->setNombre($nombre);
        $estructura->setRaiz($raiz);

        $em = $this->doctrine->getManager();
        $em->persist($estructura);
        $em->flush();
    }


    /**
     * Actualiza una estructura
     *
     * @param integer $id  El id de la estructura que se quiere actualizar
     * @param string $nombre El nuevo nombre que se le quiere asignar
     * @param string $raiz Si puede ocupar la posición inicial del árbol jerárquico
     *
     * @return bool true Si se actualizó adecuadamente
     *
     * @throws \InvalidArgumentException La estructura que intenta actualizar no existe
     *
     *
     *
     * Responde al RF (68)(Modificar estructura)
     */

    public function actualizarEstruc($id, $nombre, $raiz)
    {
        $em = $this->doctrine->getManager();
        $estructura = $em->getRepository('EyCBundle:Estructura')->find($id);

        if ($estructura != NULL)
        {
            $estructura->setNombre($nombre);
            $estructura->setRaiz($raiz);

            $error = $this->validador->validate($estructura);

            if (count($error) > 0)
            {
                return $error;
            }
            else
            {
                $em->persist($estructura);
                $em->flush();
                return true;
            }
        }
        else
            return false;
    }

    /**
     * Elimina una estructura si el id es válido
     *
     * @param int $id Id de la estructura
     *
     * @return bool true Si la estructura se eliminó satisfactoriamente
     *
     * @throws \InvalidArgumentException La estructura con el id especificado no existe
     *
     *
     *
     * Responde al RF (69)(Eliminar estructura)
     */

    public function eliminarEstruc($id)
    {
        $em = $this->doctrine->getManager();
        $estructura = $em->getRepository('EyCBundle:Estructura')->find($id);
        if ($estructura != NULL)
        {
            $em->remove($estructura);
            $em->flush();
            return true;
        }
        else
//            throw new \InvalidArgumentException("La estructura con el id especificado no existe.");
            return false;

    }

    /**
     * Elimina una relacion entre estructura hija y padre si el id es válido
     *
     * @param int $id Id de la estructura hija
     *
     * @return bool true Si la estructura hija se eliminó satisfactoriamente
     *
     * @throws \InvalidArgumentException La estructura con el id especificado no existe
     *
     *
     *
     * Responde al RF (75)(Eliminar  relacion estructura hija)
     */

    public function eliminarRelacionEstructura($id_hija,$id_padre)
    {
        $em = $this->doctrine->getManager();

        $estructuraHija = $em->getRepository('EyCBundle:Estructura')->find($id_hija);

        $estructuraPadre = $em->getRepository('EyCBundle:Estructura')->find($id_padre);


        if($estructuraPadre != null){

            if($estructuraHija != null){
                $esHija = $this->esHijadeEstru($id_hija,$id_padre);
                if($esHija == 1){
                    $estructuraPadre->removeEstructurasHija($estructuraHija);
                    $em->flush();
                    return 1;
                }else{
                    return 4;
                }
            }else{
                return 3;
            }
        }else{
            return 2;
        }
    }



    /**
     * Muestra una estructura dado su id.
     *
     * @param integer $id El id de la estructura.
     * @return Estructura Si el id es válido
     *
     * @throws \InvalidArgumentException  La Estructura con el id especificado no existe.
     *
     */

    public function mostrarEstruc($ide)
    {
        $em = $this->doctrine->getManager();
        $estructura = $em->getRepository('EyCBundle:Estructura')->find($ide);

        if ($estructura != NULL)
        {
            return $estructura;
        }
        else
            return 2;
//            throw new \InvalidArgumentException("La estructura con el id especificado no existe.");
    }




    /**
     * Muestra todas las estructuras.
     *
     *
     * @return Estructura Una lista de las Estructuras.
     *
     */
    public function mostrarEstrucs()
    {
        $em = $this->doctrine->getManager();
        $estructuras = $em->getRepository('EyCBundle:Estructura')->findAll();

        if ($estructuras != NULL)
        {
            return $estructuras;

        }
        else
            return 2;
//            throw new \InvalidArgumentException("No existen estructuras registradas.");
    }

    /**
     * Muestra 1 si es hija del padre pasado.
     *
     *
     * @return Estructura Una lista de las Estructuras.
     *
     */
    public function esHijadeEstru($id_hija,$id_padre)
    {
        $em = $this->doctrine->getManager();
        $estructura = $em->getRepository('EyCBundle:Estructura')->find($id_padre);
        $esHija=0;

        foreach($estructura->getEstructurasHijas() as $hija)
        {
            if($hija->getId() == $id_hija){
                $esHija = 1;
            }
        }
        return $esHija;
    }


    /*********************************** termina la gestión de las estructuras **********************************/





////////////////////////////////////// Estas funciones gestionan los  campos de las estructuras/////////////



    /**
     * Inserta un campo de una estructura.
     *
     * @param integer $ide  El id de la estructura al que se le quiere adicionar el campo.
     * @param string $nombre Nombre del campo.
     * @param string $tipodato   El tipo de dato que tomarán los valores de ese campo
     * @param string $descripcion Descripción del campo
     * @param bool $vinculado Si el campo está vinculado a un nomenclador.
     * @param integer $nomencladorvin El id del nomenclador con el que está vinculado el campo.
     *
     * @return bool true Si el campo se insertó satisfactoriamente o ConstraintViolationListInterface una
     *         lista de violaciones de restricciones a la hora de insertar el campo.
     *
     * @throws \InvalidArgumentException Si la estructura  que intenta insertarle un campo no existe.
     *
     *
     *
     * Responde al RF (71)(Adicionar campos a estructura)
     */

    public function insertarCampoEstruc($ide, $nombre, $tipodato, $descripcion, $vinculado, $nomencladorvin = 0)
    {
        $em = $this->doctrine->getManager();
        $estructura = $em->getRepository('EyCBundle:Estructura')->find($ide);

        if ($estructura != NULL)
        {
            $campoestruc = new CampoEstruc();

            $campoestruc->setNombre($nombre);
            $campoestruc->setTipoDato($tipodato);
            $campoestruc->setDescripcion($descripcion);
            $campoestruc->setVinculado($vinculado);
            $campoestruc->setNomenclador($nomencladorvin);
            $campoestruc->setEstructura($estructura);

            $error = $this->validador->validate($campoestruc);

            if (count($error) > 0)
            {
                return 3;
            }
            else
            {
                $em->persist($campoestruc);
                $em->flush();
                $campos = $em->getRepository('EyCBundle:CampoEstruc')->createQueryBuilder('e')
                    ->where('e.estructura =:id')
                    ->setParameter('id', $ide)
                    ->getQuery()
                    ->getArrayResult();

                $idCampo = $campos[count($campos)-1]['id'];
                $camposvalores = $em->getRepository('EyCBundle:EstructuraOp')->findByMostrarValoresCamposEstrucOp2($ide);
                for($i = 0; $i< count($camposvalores['valores']); $i++){
                    $this->insertarValEstrucOp($camposvalores['valores'][$i]['IdEop'],$idCampo,'');
                }
                return 1;
            }
        }
        else
//            throw new \InvalidArgumentException("La estructura a la que intenta insertarle un campo no existe.");

            return 2;
    }


    /**
     * Elimina un campo de una estructura
     *
     * @param int $id El id del campo que desea eliminar
     * @return bool  true Si el campo se eliminó satisfactoriamente
     *
     * @throws \InvalidArgumentException Si el campo de la estructura con el idc especificado no existe.
     *
     *
     *
     * Responde al RF (73)(Eliminar campos a estructura)
     */

    public function eliminarCampoEstruc($ide)
    {
        $em = $this->doctrine->getManager();
        $campoestruc = $em->getRepository('EyCBundle:CampoEstruc')->find($ide);
        if ($campoestruc != NULL)
        {
            $em->remove($campoestruc);
            $em->flush();
            return 1;
        }
        else
//            throw new \InvalidArgumentException("El campo de la estructura con el idc especificado no existe.");

            return 2;
    }


    /**
     * Actualiza los campos de una estructura
     *
     * @param integer $idc  El id del campo que se le quiere actualizar.
     * @param string $nombre Nombre del campo.
     * @param string $tipodato   El tipo de dato que tomarán los valores de ese campo
     * @param string $descripcion Descripción del campo
     * @param bool $vinculado Si el campo está vinculado a un nomenclador.
     * @param integer $nomencladorvin El id del nomenclador con el que está vinculado el campo.

     *
     * @return bool true Si el campo se actualizó satisfactoriamente o ConstraintViolationListInterface una
     *         lista de violaciones de restricciones a la hora de actualizar el campo
     *
     * @throws \InvalidArgumentException Si el campo del nomenclador que intenta actualizar no existe o
     *         el nomenclador  no existe
     *
     *
     *
     * Responde al RF (72)(Modificar campos a estructura)
     */

    public function actualizarCampoEstruc($idc, $nombre, $tipodato, $descripcion, $vinculado, $nomencladorvin)
    {
        $em = $this->doctrine->getManager();
        $campoestruc = $em->getRepository('EyCBundle:CampoEstruc')->find($idc);

        if ($campoestruc != NULL)
        {
            $campoestruc->setNombre($nombre);
            $campoestruc->setTipoDato($tipodato);
            $campoestruc->setDescripcion($descripcion);
            $campoestruc->setVinculado($vinculado);
            $campoestruc->setNomenclador($nomencladorvin);


            $error = $this->validador->validate($campoestruc);

            if (count($error) > 0)
            {
                return 3;
            }
            else
            {
                $em->persist($campoestruc);
                $em->flush();
                return 1;
            }
        }
        else
            return 2;
//                throw new \InvalidArgumentException("El campo de la estructura que intenta actualizar no existe.");

        return true;
    }



    /**
     * Muestra los campos de una estructura
     *
     * @param int $ide  El id de la estructura que se quiere mostrar sus campos.
     *
     * @return Una lista de campos
     *
     * @throws \InvalidArgumentException Si la estructura con el ide especificado no existe o
     *          no posee campos.
     *
     */

    public function mostrarCamposEstruc($ide)
    {
        $em = $this->doctrine->getManager();

        $camposestruc = $em->getRepository('EyCBundle:CampoEstruc')->findBy(array('estructura' => $ide));

        if ($camposestruc != 2)
        {
            return $camposestruc;
        }
        else
            return 2;
//                throw new \InvalidArgumentException("La estructura especificada no posee campos.");

    }



    /**
     * Muestra un campo de una estructura.
     *
     * @param int $idc  El id del campo de la estructura que se quiere mostrar.
     *
     * @return CampoEstruc  Un campo de una estructura si está registrado.
     *
     * @throws \InvalidArgumentException Si el campo de la estructura con el id especificado no existe.
     *
     *
     *
     *
     */

    public function mostrarCampoEstruc($idc)
    {
        $em = $this->doctrine->getManager();
        $campoestruc = $em->getRepository('EyCBundle:CampoEstruc')->find($idc);

        if ($campoestruc != NULL)
        {
            return $campoestruc;

        }
        else
            throw new \InvalidArgumentException("El campo de la estructura con el idc especificado no existe.");
    }


    /*********************************** termina la gestion de los campos de las estructuras **********************************/



///////////////////////////////////////// Estas funciones gestionan las  EstructurasOP/////////////////////////////



    /**
     * Insertar una instancia de una estructura.
     *
     * @param int $ide  El id de la estructura de la que se quiere insertar una instancia.
     * @param string $nombre El nombre que se le quiere dar a la instancia de la estructura.
     * @param array $valores   Un arreglo que contiene los id de los campos y los valores correspondientes.
     *                         array(array('id' => idCampo1, 'valor' => valorCampo1), array('id' => idCampo2, 'valor' => valorCampo2)...)
     *
     * @return bool true Si la instancia se insertó satisfactoriamente o ConstraintViolationListInterface una
     *         lista de violaciones de restricciones a la hora de insertar la instancia de la estructura.
     *
     * @throws \InvalidArgumentException Si la estructura  que intenta insertarle un campo no existe.
     *
     *
     *
     * Responde al RF (79)(Adicionar instancia a estructura)
     */

    public function insertarEstrucOp($ide, $nombre,  $valores,  $idep)
    {
        $em = $this->doctrine->getManager();
        $estructura  = $em->getRepository('EyCBundle:Estructura')->find($ide);
        $camposestruc = $this->mostrarCamposEstruc($ide);
        $estructurapadre = NULL;
        $valoresObjec = array();


        if ($estructura != NULL  && $camposestruc != NULL)
        {

            $estructuraop = new EstructuraOp();
            $estructuraop->setNombre($nombre);
            $estructuraop->setEstructura($estructura);

            if ($idep == 0 && !$estructura->getRaiz())
            {
                return 2;
//                    throw new \InvalidArgumentException("Esta estructura no puede ser raíz.");
            }
            if ($idep != 0)
            {
                $estructurapadre = $this->mostrarEstrucOp($idep);
//                print_r($estructuraop);
                $estructurapadre->addChildren($estructuraop);
            }

            $estructuraop->setParent($estructurapadre);

            if ($idep != 0 && !$this->esEstrucPadre($estructurapadre->getEstructura()->getId(), $ide) )
            {
                return 3;
//                    throw new \InvalidArgumentException("La estructura no es subordinada.");
            }

            $error = $this->validador->validate($estructuraop);

            if (count($error) == 0)
            {

                if (is_array($valores) && is_array(current($valores)))
                {

                    $em->persist($estructuraop);
                    $valoresObjec[] = $estructuraop;


                    $countcamposestruc = count($camposestruc);
                    $countvalores = count($valores);


                    if ($countcamposestruc > 0 && $countcamposestruc == $countvalores)
                    {

                        $camposinsertados = 0;

                        foreach ($camposestruc as $campo)
                        {
                            foreach ($valores as $valor)
                            {
                                if ($campo->getId() == $valor['id'])
                                {
                                    if ($this->validarTipoValor($campo->getTipoDato(), $valor['valor']))
                                    {
                                        $campoestruc = new ValoresEstruc();
                                        $campoestruc->setCampoEstruc($campo);
                                        $campoestruc->setEstructuraOp($estructuraop);
                                        $campoestruc->setValor($valor['valor']);

                                        $error = $this->validador->validate($campoestruc);

                                        if (count($error) == 0)
                                        {
                                            $em->persist($campoestruc);
                                            $valoresObjec[] = $campoestruc;
                                            $camposinsertados++;
                                            break;
                                        }
                                        else {
                                            foreach ($valoresObjec as $objec)
                                            {

                                                $em->clear($objec);
                                            }
                                            return $error;
                                        }
                                    }

                                }
                            }
                        }

                        if ($camposinsertados == $countcamposestruc)
                        {
                            $em->flush();
                            return 1;

                        }
                        else
                        {
                            foreach ($valoresObjec as $objec)
                            {

                                $em->clear($objec);
                            }
                            return 4;
                            //throw new \InvalidArgumentException("Los valores insertados no coinciden con los campos de la estructura.");
                        }
                    }
                    else
                    {
                        foreach ($valoresObjec as $objec)
                        {

                            $em->clear($objec);
                        }
                        return 5;
                        //throw new \InvalidArgumentException("El arreglo de valores no coincide con los campos de la estructura.");
                    }
                }
                else
                    return 6;
//                        throw new \InvalidArgumentException("El arreglo de valores no tiene la estructura esperada.");
            }
            else
                return $error;
        }
        else
            return 7;
        //throw new \InvalidArgumentException("La estructura de la que se quiere insertar una instancia no existe o no posee campos.");
    }


    /**
     * Actualizar una instancia de una estructura.
     *
     * @param int $ideop  El id de la instancia de la estructura que se quiere actualizar.
     * @param string $nombre Nombre de la instancia de la estructura que se quiere actualizar.
     * @param array $valores   Un arreglo que contiene los id de los campos y los valores correspondientes
     *        de la instancia de la estructura que se quiere actualizar.
     *        array(array('id' => idCampo1, 'valor' => valorCampo1), array('id' => idCampo2, 'valor' => valorCampo2)...)
     *
     * @return bool true Si el campo se actualizó correctamente o ConstraintViolationListInterface una
     *         lista de violaciones de restricciones a la hora de actualizar la instancia de la estructura.
     *
     *
     * @throws \InvalidArgumentException Si la estructura que intenta insertarle un campo no existe.
     *
     *
     *
     * Responde al RF (80)(Modificar instancia a estructura)
     */

    public function actualizarEstrucOp($ideop,$idp, $nombre, $valores)
    {
        $em = $this->doctrine->getManager();
        $estructuraop = $em->getRepository('EyCBundle:EstructuraOp')->find($ideop);
//        $estructurapadre = $em->getRepository('EyCBundle:EstructuraOp')->find($idp);
        $valoresObjec = array();

        if ($estructuraop != NULL)
        {
            $camposestruc = $this->mostrarCamposEstruc($estructuraop->getEstructura());

            if ($camposestruc != NULL)
            {
//                $estructuraop->setParent($estructurapadre);
                $estructuraop->setNombre($nombre);

                $error = $this->validador->validate($estructuraop);

                if (count($error) == 0)
                {
                    if (is_array($valores) && is_array(current($valores)))
                    {
                        $em->persist($estructuraop);
                        $valoresObjec[] = $estructuraop;

                        $countcamposestruc = count($camposestruc);
                        $countvalores = count($valores);

                        if ($countcamposestruc > 0 && $countcamposestruc == $countvalores)
                        {
                            $camposinsertados = 0;

                            foreach ($camposestruc as $campo) {
                                foreach ($valores as $valor) {
                                    if ($campo->getId() == $valor['id'])
                                    {
                                        if ($this->validarTipoValor($campo->getTipoDato(), $valor['valor']))
                                        {

                                            $valoresestruc = $em->getRepository('EyCBundle:ValoresEstruc')
                                                ->findOneBy(array('estructuraOp' => $estructuraop->getId(),
                                                    'campoEstruc' => $campo->getId()));

                                            if($valoresestruc) {

                                                $valoresestruc->setValor($valor['valor']);

                                                $em->persist($valoresestruc);

                                                $valoresObjec[] = $valoresestruc;
                                            } else{
                                                //insertar en caso de que venga vacio
                                                $this->insertarValEstrucOp($ideop,$valor['id'],$valor['valor']);
                                            }
                                            $camposinsertados++;
                                            break;
                                        }

                                    }
                                }
                            }
                            if ($camposinsertados == $countcamposestruc)
                            {
                                $em->flush();
                                return 1;
                            }
                            else
                            {
                                foreach ($valoresObjec as $objec)
                                {

                                    $em->clear($objec);
                                }
                                return 2;
//                                throw new \InvalidArgumentException("El arreglo de valores no coincide con los campos de la estructura.");
                            }
                        }
                        else
                        {
                            return 3;
//                            throw new \InvalidArgumentException("El arreglo de valores no coincide con los campos del nomenclador.");
                        }
                    } else
                        return 4;
//                        throw new \InvalidArgumentException("El arreglo de valores no tiene la estructura esperada.");

                }
                else
                    return $error;
            } else
                return 5;
//                throw new \InvalidArgumentException("No se puede obtener campos para la instancia del nomenclador especificada.");

        }
        else
            return 6;
//            throw new \InvalidArgumentException("No se puede obtener una instancia del nomenclador especificado.");

    }


    public function insertarValEstrucOp($ideop,$idcampo,$valor)
    {

        $em = $this->doctrine->getManager();
        $estructura_op = $em->getRepository('EyCBundle:EstructuraOp')->find($ideop);
        $campo_estructura = $em->getRepository('EyCBundle:CampoEstruc')->find($idcampo);

        $new = new ValoresEstruc();
        $new->setEstructuraOp($estructura_op);
        $new->setCampoEstruc($campo_estructura);
        $new->setValor($valor);

        $em->persist($new);
        $em->flush();

    }

    /**
     * Eliminar una instancia de una estructura.
     *
     * @param int $ideop  El id de la instancia de la estructura que se quiere eliminar.
     *
     * @return bool true Si la instancia de la estructura se eliminó satisfactoriamente.
     *
     * @throws \InvalidArgumentException Si la instancia de la estructura con el id especificado no existe.
     *
     *
     *
     * Responde al RF (81)(Eliminar instancia a estructura)
     */

    public function eliminarEstrucOp($ideop)
    {
        $em = $this->doctrine->getManager();
        $estrucop = $em->getRepository('EyCBundle:EstructuraOp')->find($ideop);
        if ($estrucop != NULL)
        {
            $em->remove($estrucop);
            $em->flush();
            return 1;
        }
        else
//            throw new \InvalidArgumentException("La instancia de la estructura con el id especificado no existe.");
            return 2;
    }



    /**
     * Mostrar una instancia de una estructura.
     *
     */

    public function mostrarEstrucOp($ideop)
    {
        $em = $this->doctrine->getManager();
        $estrucop = $em->getRepository('EyCBundle:EstructuraOp')->find($ideop);


        if ($estrucop != NULL)
        {
            return $estrucop;
        }else{
            return false;
        }

    }


    /**
     * Muestra todas las instancia de una estructura.
     *
     * @param int $ide  El id de la estructura que se quiere mostrar.
     *
     * @return array Un arreglo que en cada posición contiene un arreglo donde en array['nom'] contiene la
     *         instancia del nomenclador y array['campos'] contiene los campos del nomenclador y sus valores.
     *
     */

    public function mostrarEstrucOps($ide)
    {
        $em = $this->doctrine->getManager();
        $estructura = $em->getRepository('EyCBundle:Estructura')->find($ide);

        if ($estructura != NULL)
        {
            $camposestruc = $em->getRepository('EyCBundle:EstructuraOp')->findBy(Array('estructura' => $estructura->getId()));

            if ($camposestruc != NULL)
            {
                return $camposestruc;
            }
            else
                throw new \InvalidArgumentException("La  estructura especificada no posee campos.");
        }
        else
            throw new \InvalidArgumentException("La estructura con el idn especificado no existe.");

    }


    /**
     * Muestra  las estructura raíz.
     *
     * @return Una colección con todas las estructuras raíces o NULL si no existen.
     *
     */

    public function mostrarEstrucRaizOp()
    {
        $em = $this->doctrine->getManager();
        $estructura = NULL;

        $estructura = $em->getRepository('EyCBundle:EstructuraOp')->findMostrarEstrucRaizOp();

        return $estructura;
    }


    /**
     * Mostrar valores de una instancia de una estructura.
     *
     * @param int $ideop  El id de la instancia del nomenclador que se quiere mostrar.
     *
     * @return array Un arreglo que en la primera posición array['estruc'] contiene la instancia de la estructura y
     *         array['campos'] contiene los campos de la estructura y sus valores.
     *
     */

    public function mostrarValoresCamposEstrucOp($ideop,$order, $page, $limit, $filter,$cantCampos,$direction)
    {

        $em = $this->doctrine->getManager();

        $camposvalores = $em->getRepository('EyCBundle:EstructuraOp')->findByMostrarValoresCamposEstrucOp($ideop,$order, $page, $limit, $filter,$cantCampos,$direction);

//        $camposvalores= $em->getRepository('EyCBundle:EstructuraOp')->findMostrarEstrucRaizOp();
        if ($camposvalores != NULL)
        {
            return $camposvalores;
        }
        else
            return false;
    }

    public function mostrarValoresCamposEstrucOp2($ideop)
    {

        $em = $this->doctrine->getManager();

        $camposvalores = $em->getRepository('EyCBundle:EstructuraOp')->findByMostrarValoresCamposEstrucOp2($ideop);

//        $camposvalores= $em->getRepository('EyCBundle:EstructuraOp')->findMostrarEstrucRaizOp();
        if ($camposvalores != NULL)
        {
            return $camposvalores;
        }
        else
            return false;
    }
    /**
     * Mostrar id, nombre de instancia de una estructura.
     *
     */

    public function mostrarNomeOpsByEstructura($id)
    {

        $em = $this->doctrine->getManager();
//        $estrucop = $em->getRepository('EyCBundle:EstructuraOp')->findEstOpByEstructura($id);
        $estrucop = $em->getRepository('EyCBundle:EstructuraOp')->findEstrucOp($id,'');

        if ($estrucop != NULL)
        {
            return $estrucop;
        }
        else
            return false;
    }



    /*********************************** termina la gestión de los EstructurasOp **********************************/




///////////////////////////////////////// Estas funciones gestionan las  subordinaciones /////////////////////////////

    /**
     * Insertar estructuras subordinadas.
     *
     * @param int $id  El id de la estructura padre.
     * @param array $ids  Los ids de las estructuras hijas.
     *
     * @return bool true Si se insertaron adecuadamente
     *
     *
     *
     *
     * Responde al RF (70)(ECrear relaciones entre conceptos)
     */

    public function insertarEstrucSub($id, $ids)
    {
        $em = $this->doctrine->getManager();

        if ($id > 0 && count($ids) > 0)
        {
            $estruc = $em->getRepository('EyCBundle:Estructura')->find($id);

            if ($estruc != NULL)
            {
                foreach ($ids as $idestruc)
                {
                    $estrucsub = $em->getRepository('EyCBundle:Estructura')->find($idestruc);
                    if ($estrucsub != NULL)
                    {
                        $estruc->addEstructurasHija($estrucsub);
//                          $estrucsub->addEstructurasPadre($estruc );
                    }
                    else
                        return 1;
//                        throw new \InvalidArgumentException("Una de las estructuras subordinadas indicadas no existe.");
                }

                $em->persist($estruc);
                $em->flush();
                return 4;
            }
            else
                return 2;
//                throw new \InvalidArgumentException("La estructura a la que intenta adicionarle subordinaciones no existe.");
        }
        else
            return 3;
//            throw new \InvalidArgumentException("Datos incorrectos.");
    }


    /**
     * Verifica si idep es padre de ide
     *
     * @param int $idep  El id de la estructura padre
     * @param int $ide  El id de las estructura hija
     *
     * @return bool true si es padre, false en otro caso
     *
     */

    public function esEstrucPadre($idep, $ide)
    {
        $estructurapadre = $this->mostrarEstruc($idep);
        $estructurahija = $this->mostrarEstruc($ide);

        if ($estructurapadre != NULL && $estructurahija != NULL)
        {
            $hijasestructurapadre = $estructurapadre->getEstructurasHijas();

            foreach ($hijasestructurapadre as $hija)
            {
                if ($hija->getId() == $ide)
                    return true;
            }
        }
        return false;
    }

    //Para obtener el arbol de estructura
    public function mostrarArbolEstrucOps()
    {
        $respuesta = array();
        $estRaicesOp = $this->mostrarEstrucRaizOp();
        if($estRaicesOp != null){
            foreach ($estRaicesOp as $estructura) {
                $respuesta[] = $this->obtenerEstructuraOpArbol($estructura);
            }
        }
        return $respuesta;
    }

    private function obtenerEstructuraOpArbol(EstructuraOp $estructura)
    {

        $current = array(
            'id' => $estructura->getId(),
            'estructura' => $estructura->getEstructura()->getNombre(),
            'estructura_id' => $estructura->getEstructura()->getId(),
            'raiz'=>$estructura->getEstructura()->getRaiz(),
            'nombre' => $estructura->getNombre()
        );

        $childrens = $estructura->getChildren()->toArray();

        $hijos = array();
        foreach ($childrens as $child) {
            if($child != $estructura){
                $hijos[] = $this->obtenerEstructuraOpArbol($child);
            }

        }
        $current['children'] = $hijos;
        return $current;
    }

}

/*********************************** termina la gestión de las subordinaciones **********************************/


