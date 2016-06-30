<?php

namespace UCI\Boson\EyCBundle\Service;

use UCI\Boson\EyCBundle\Entity\CampoNom;
use UCI\Boson\EyCBundle\Entity\ValoresNom;
use UCI\Boson\EyCBundle\Entity\Nomenclador;
use UCI\Boson\EyCBundle\Entity\NomencladorOp;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Validator\Constraints\Date;
use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Symfony\Component\Validator\ValidatorInterface   as Validador;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Acl\Exception\Exception;


class NomencladorService
{

	private $doctrine;
    private $validador;

    /**
     * Constructor de la clase.
     *
     * @param Doctrine $doctrine  Contenedor de doctrine.
     * @param Validador $validador Contenedor validator.
     *
     *
     */

	public function __construct( Doctrine $doctrine, Validador $validador){
		$this->doctrine = $doctrine;
        $this->validador = $validador;
	}

    /**
     * Verifica que valor es del tipo indicado.
     *
     * @param string $tipo  Un tipo de dato válido.
     * @param string $valor Un valor dado.
     *
     * @return bool Verdadero si valor es del tipo especificado, false en otro caso.
     *
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
     * @param string $tipo  Un tipo de dato válido.
     * @param string $valor Un valor dado.
     *
     * @return valor convertido al tipo especificado, null en otro caso.
     *
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


//////////////////////////////////////////////// // Estas funciones gestionan los nomencladores////////////////////


    /**
     * Inserta un nomenclador.
     *
     * @param string $nombre  El nombre del nomenclador
     *
     * @return integer El id del nomenclador si se pudo insertar correctamente, en otro caso
     *          retorna ConstraintViolationListInterface una lista de violaciones de restricciones
     *          a la hora de insertar el nomenclador.
     *
     *
     *
     * Responde al RF (67)(Adicionar nomenclador)
     */

    public function insertarNom($nombre)
    {
        $nomenclador = new Nomenclador();
        $nomenclador->setNombre($nombre);

        $error = $this->validador->validate($nomenclador);

        if (count($error) > 0)
        {
           return 2;
        }
        else
        {
            $em = $this->doctrine->getManager();
            $em->persist($nomenclador);
            $em->flush();
            return 1;
        }
	}

    /**
     * Elimina un nomenclador si el id es válido.
     *
     * @param int $id  Id del nomenclador
     *
     * @return bool true Si el nomenclador se eliminó satisfactoriamente.
     *
     * @throws \InvalidArgumentException El nomenclador con el id especificado no existe.
     *
     *
     *
     * Responde al RF (69)(Eliminar nomenclador)
     */

    public function eliminarNom($id)
    {
        $em = $this->doctrine->getManager();
        $nomenclador = $em->getRepository('EyCBundle:Nomenclador')->find($id);
        if ($nomenclador != NULL)
        {
            $em->remove($nomenclador);
            $em->flush();
            return 1;
        }
        else
          return 2;
	}

    /**
     * Actualiza un nomenclador.
     *
     * @param integer $id  El id del nomenclador que se quiere actualizar.
     * @param string $nombre el nuevo nombre que se le quiere asignar.
     *
     * @return bool true Si se actualizó adecuadamente.
     *
     * @throws \InvalidArgumentException El nomenclador que intenta actualizar no existe.
     *
     *
     *
     * Responde al RF (68)(Modificar nomenclador)
     */

    public function actualizarNom($id, $nombre)
    {
        $em = $this->doctrine->getManager();
        $nomenclador = $em->getRepository('EyCBundle:Nomenclador')->find($id);

        if ($nomenclador != NULL)
        {
            $nomenclador->setNombre($nombre);

            $error = $this->validador->validate($nomenclador);

            if (count($error) > 0)
            {
                return 3;
            }
            else
            {

                $em->persist($nomenclador);
                $em->flush();
                return 1;
            }
        }
        else
            return 2;
//            throw new \InvalidArgumentException("El nomenclador que intenta actualizar no existe.");
    }

    /**
     * Muestra un nomenclador dado su id.
     *
     * @param integer $id  El id del nomenclador.
     * @return Nomenclador Si el id es válido.
     *
     * @throws \InvalidArgumentException  El nomenclador con el id especificado no existe.
     *
     *
     */

    public function mostrarNom($id)
    {
        $em = $this->doctrine->getManager();
        $nomenclador = $em->getRepository('EyCBundle:Nomenclador')->find($id);
        if ($nomenclador != NULL)
        {
            return $nomenclador;

        }

           // throw new \InvalidArgumentException("El nomenclador con el id especificado no existe.");
	}

    /**
     * Muestra todos los nomencladores.
     *
     *
     * @return Nomenclador Una lista de los nomencladores.
     *
     *
     */

    public function mostrarNoms()
    {
        $em = $this->doctrine->getManager();
        $nomencladores = $em->getRepository('EyCBundle:Nomenclador')->findAll();

        if ($nomencladores != NULL)
        {
            return $nomencladores;
        }
        else
            return 2;
//            throw new \InvalidArgumentException("No existen nomencladores registrados.");
    }


/*********************************** termina la gestión de los nomenladores **********************************/


    
////////////////////////////////////// Estas funciones gestionan los  campos de los nomencladores/////////////



    /**
     * Inserta un campo de un nomenclador.
     *
     * @param integer $idn  El id del nomenclador al que se le quiere adicionar el campo
     * @param string $nombre nombre del campo
     * @param string $tipodato   El tipo de dato que tomarán los valores de ese campo
     * @param string $descripcion descripción del campo
     * @param bool $vinculado Si el campo está vinculado a un nomenclador
     * @param integer $nomencladorvin El id del nomenclador con el que está vinculado el campo
     *
     * @return bool true Si el campo se insertó satisfactoriamente o ConstraintViolationListInterface una
     *         lista de violaciones de restricciones a la hora de insertar el campo
     *
     * @throws \InvalidArgumentException Si al nomenclador  que intenta insertarle un campo no existe
     *
     *
     *
     * Responde al RF (71)(Adicionar campos a nomenclador)
     */

    public function insertarCampoNom($idn, $nombre, $tipodato, $descripcion, $vinculado, $nomencladorvin = 0)
    {
        $em = $this->doctrine->getManager();
        $nomenclador = $em->getRepository('EyCBundle:Nomenclador')->find($idn);

        if ($nomenclador != NULL)
        {
            $camponom = new CampoNom();

            $camponom->setNombre($nombre);
            $camponom->setTipoDato($tipodato);
            $camponom->setDescripcion($descripcion);
            $camponom->setVinculado($vinculado);
            $camponom->setNomencladorVin($nomencladorvin);
            $camponom->setNomenclador($nomenclador);

            $error = $this->validador->validate($camponom);

            if (count($error) > 0)
            {
                return 3;
            }
            else
            {
                $em->persist($camponom);
                $em->flush();
                $campos = $em->getRepository('EyCBundle:CampoNom')->createQueryBuilder('e')
                    ->where('e.nomenclador =:id')
                    ->setParameter('id', $idn)
                    ->getQuery()
                    ->getArrayResult();

                $idCampo = $campos[count($campos)-1]['id'];
                $camposvalores = $em->getRepository('EyCBundle:NomencladorOp')->findByMostrarValoresCamposNomOp2($idn);
                for($i = 0; $i< count($camposvalores['valores']); $i++){
                    $this->insertarValNomOp($camposvalores['valores'][$i]['IDNOP'],$idCampo,'');
                }
                return 1;
            }
        }
        else
//            throw new \InvalidArgumentException("El nomenclador al que intenta insertarle un campo no existe.");
return 2;
	}


    /**
     * Elimina un campo de un nomenclador.
     *
     * @param int $ids  el id del campo que se quiere eliminar
     *
     * @return bool true Si el campo se eliminó satisfactoriamente
     *
     * @throws \InvalidArgumentException Si el campo del nomenclador con el idc especificado no existe.
     *
     *
     *
     * Responde al RF (73)(Eliminar campos a nomenclador)
     *
     */

    public function eliminarCampoNom($idc)
    {
        $em = $this->doctrine->getManager();
        $camponom = $em->getRepository('EyCBundle:CampoNom')->find($idc);
        if ($camponom != NULL)
        {
            $em->remove($camponom);
            $em->flush();
            return 1;
        }

	}


    /**
     * Actualiza los campos de un nomenclador.
     *
     * @param integer $idc  El id del campo que se le quiere actualizar
     * @param string $nombre nombre del campo
     * @param string $tipodato   El tipo de dato que tomarán los valores de ese campo
     * @param string $descripcion descripción del campo
     * @param bool    $vinculado Si el campo está vinculado a un nomenclador
     * @param integer $nomencladorvin El id del nomenclador con el que está vinculado el campo.
     *
     * @return bool true Si el campo se insertó satisfactoriamente o ConstraintViolationListInterface una
     *         lista de violaciones de restricciones a la hora de insertar el campo.
     *
     * @throws \InvalidArgumentException Si el campo del nomenclador que intenta actualizar no existe o
     *         el nomenclador no existe.
     *
     *
     *
     * Responde al RF (72)(Modificar campos a nomenclador)
     */

    public function actualizarCampoNom($idc, $nombre, $tipodato, $descripcion, $vinculado, $nomencladorvin)
    {
        $em = $this->doctrine->getManager();

            $camponom = $em->getRepository('EyCBundle:CampoNom')->find($idc);

            if ($camponom != NULL)
            {
                $camponom->setNombre($nombre);
                $camponom->setTipoDato($tipodato);
                $camponom->setDescripcion($descripcion);
                $camponom->setVinculado($vinculado);
                $camponom->setNomencladorVin($nomencladorvin);

                $error = $this->validador->validate($camponom);

                if (count($error) > 0)
                {
//                    return $error;
                    return 3;
                }
                else
                {
                    $em->persist($camponom);
                    $em->flush();
                    return 1;
                }

            }
            else
                return 2;
//                throw new \InvalidArgumentException("El campo del nomenclador que intenta actualizar no existe.");

    }



    /**
     * Muestra un campo de un nomenclador.
     *
     * @param int $idc  El id del nomenclador que se quiere mostrar.
     *
     * @return Nomenclador  Un nomenclador si está registrado.
     *
     * @throws \InvalidArgumentException Si el campo del nomenclador con el idc especificado no existe.
     *
     *
     */

    public function mostrarCampoNom($idc)
    {
        $em = $this->doctrine->getManager();
        $camponom = $em->getRepository('EyCBundle:CampoNom')->find($idc);

        if ($camponom != NULL)
        {
            return $camponom;

        }
//            //throw new \InvalidArgumentException("El campo del nomenclador con el idc especificado no existe.");
	}

    /**
     * Muestra los campos de un nomenclador.
     *
     * @param int $idn  El id del nomenclador del que se quieren mostrar sus campos.
     *
     * @return Una lista de campos
     *
     * @throws \InvalidArgumentException Si el nomenclador con el idn especificado no existe o
     *         el  nomenclador especificado no posee campos.
     *
     *
     */

    public function mostrarCamposNom($idn)
    {
           $em = $this->doctrine->getManager();

           $camposnom = $em->getRepository('EyCBundle:CampoNom')->findBy(array('nomenclador' => $idn));

            if ($camposnom != NULL)
            {
                return $camposnom;
            }
            else
                return array();
//                throw new \InvalidArgumentException("El  nomenclador especificado no posee campos o no existe.");
    }


/*********************************** termina la gestión de los campos de nomenladores **********************************/



///////////////////////////////////////// Estas funciones gestionan los  nomencladorOP/////////////////////////////



    /**
     * Insertar una instancia de un nomenclador.
     *
     * @param int $idn  El id del nomenclador del que se quiere insertar una instancia.
     * @param string $nombre El nombre que se le quiere dar a la instancia del nomenclador.
     * @param array $valores   Un arreglo que contiene los id de los campos y los valores correspondientes en el formato 'id'=>'id del campo','valor'.
     *
     * @return bool true Si la instancia se insertó satisfactoriamente o ConstraintViolationListInterface una
     *         lista de violaciones de restricciones a la hora de insertar la instancia del nomenclador.
     *
     * @throws \InvalidArgumentException Si al nomenclador  que intenta insertarle un campo no existe.
     *
     *
     *
     * Responde al RF (79)(Adicionar instancia a nomenclador)
     */

    public function insertarNomOp($idn, $nombre,  $valores)
    {

        $em = $this->doctrine->getManager();
        $camposnom = $this->mostrarCamposNom($idn);
        $valoresObjec = array();

        if (count($camposnom) > 0)
        {
            $nommenclador = $this->mostrarNom($idn);
            $nomencladorop = new NomencladorOp();
            $nomencladorop->setNombre($nombre);
            $nomencladorop->setNomenclador($nommenclador);

            $error = $this->validador->validate($nomencladorop);
            if (count($error) == 0)
            {
                if (is_array($valores) && is_array(current($valores)))
                {
                    $em->persist($nomencladorop);
                    $valoresObjec[] = $nomencladorop;

                    $countcamposnom = count($camposnom);
                    $countvalores = count($valores);
                    if ($countcamposnom > 0 && $countcamposnom == $countvalores)
                    {
                        $camposinsertados = 0;
                        foreach ($camposnom as $campo)
                        {
                            foreach ($valores as $valor)
                            {
                                if ($campo->getId() == $valor['id'])
                                {
                                    if ($this->validarTipoValor($campo->getTipoDato(), $valor['valor']))
                                    {
                                        $camponom = new ValoresNom();
                                        $camponom->setCampoNom($campo);
                                        $camponom->setNomencladorOp($nomencladorop);
                                        $camponom->setValor($valor['valor']);

                                        $error = $this->validador->validate($camponom);
                                        if (count($error) == 0)
                                        {
                                            $em->persist($camponom);
                                            $valoresObjec[] = $camponom;
                                            $camposinsertados++;
                                            break;
                                        }
                                        else
                                        {
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

                        if ($camposinsertados == $countcamposnom)
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
//                            throw new \InvalidArgumentException("Los valores insertados no coincide con los campos del nomenclador.");
                        }
                    }
                    else
                    {
                        foreach ($valoresObjec as $objec)
                        {

                            $em->clear($objec);
                        }
                          return 3;
//                        throw new \InvalidArgumentException("El arreglo de valores no coincide con los campos del nomenclador.");
                    }
                }
                else
                       return 4;
//                    throw new \InvalidArgumentException("El arreglo de valores no tiene la estructura esperada.");
            }
           else
               return $error;
        }
        else
            return 5;
//            throw new \InvalidArgumentException("No se puede obtener una instancia del nomenclador especificado.");

    }


    /**
     * Actualizar una instancia de un nomenclador .
     *
     * @param int $idnop  El id de la instancia del nomenclador que se quiere actualizar.
     * @param string $nombre Nombre de la instancia del nomenclador que se quiere actualizar.
     * @param array $valores   Un arreglo que contiene los id de los campos y los valores correspondientes
     *        de la instancia del nomenclador que se quiere actualizar.
     *
     * @return bool true Si el campo se actualizó correctamente o ConstraintViolationListInterface una
     *         lista de violaciones de restricciones a la hora de actualizar la instancia del nomenclado.
     *
     *
     * @throws \InvalidArgumentException Si al nomenclador  que intenta insertarle un campo no existe.
     *
     *
     *
     * Responde al RF (80)(Modificar instancia a nomenclador)
     */

    public function actualizarNomOp($idnop, $nombre, $valores)
    {
        $em = $this->doctrine->getManager();
        $nomencladorop = $em->getRepository('EyCBundle:NomencladorOp')->find($idnop);
        $valoresObjec = array();

        if ($nomencladorop != NULL)
        {
            $camposnom = $this->mostrarCamposNom($nomencladorop->getNomenclador());

            if ($camposnom != NULL)
            {
                $nomencladorop->setNombre($nombre);

                $error = $this->validador->validate($nomencladorop);

                if (count($error) == 0)
                {
                    if (is_array($valores) && is_array(current($valores)))
                    {
                        $em->persist($nomencladorop);
                        $valoresObjec[] = $nomencladorop;

                        $countcamposnom = count($camposnom);
                        $countvalores = count($valores);

                        if ($countcamposnom > 0 && $countcamposnom == $countvalores)
                        {
                            $camposinsertados = 0;


                            foreach ($camposnom as $campo)
                            {
                                foreach ($valores as $valor) {

                                    if ($campo->getId() == $valor['id'])
                                    {

                                        if ($this->validarTipoValor($campo->getTipoDato(), $valor['valor']))
                                        {

                                            $valoresnom = $em->getRepository('EyCBundle:ValoresNom')
                                                             ->findOneBy(array('nomencladorOp' => $nomencladorop->getId(),
                                                                            'campoNom' => $campo->getId()));

                                        if($valoresnom){
                                            $valoresnom->setValor($valor['valor']);

                                            $em->persist($valoresnom);
                                            $valoresObjec[] = $valoresnom;
                                        }else{
                                            //insertar en caso de que venga vacio
                                            $this->insertarValNomOp($idnop,$valor['id'],$valor['valor']);
                                        }


                                            $camposinsertados++;
                                            break;
                                        }

                                    }
                                }
                            }


                            if ($camposinsertados == $countcamposnom)
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
//                                throw new \InvalidArgumentException("Los valores insertados no coinciden con los campos del nomenclador.");
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
                    return 4;//aki se rompe
            } else
                return 5;
//                throw new \InvalidArgumentException("No se puede obtener campos para la instancia del nomenclador especificada.");

        }
        else
            return 6;
//            throw new \InvalidArgumentException("No se puede obtener una instancia del nomenclador especificado.");
    }


    public function insertarValNomOp($idnop,$idcampo,$valor)
    {
        $em = $this->doctrine->getManager();
        $nomenclador_op = $em->getRepository('EyCBundle:NomencladorOp')->find($idnop);
        $campo_nomenclador = $em->getRepository('EyCBundle:CampoNom')->find($idcampo);

        $new = new ValoresNom();
        $new->setNomencladorOp($nomenclador_op);
        $new->setCampoNom($campo_nomenclador);
        $new->setValor($valor);

        $em->persist($new);
        $em->flush();

    }

    /**
     * Eliminar una instancia de un nomenclador.
     *
     * @param int $idnop  El id de la instancia del nomenclador que se quiere eliminar.
     *
     * @return bool true si la instancia de nomenclador se eliminó satisfactoriamente.
     *
     * @throws \InvalidArgumentException Si la instancia de nomenclador con el id especificado no existe.
     *
     *
     *
     * Responde al RF (81)(Eliminar instancia a nomenclador)
     */

    public function eliminarNomOp($idnop)
    {
        $em = $this->doctrine->getManager();
        $nomop = $em->getRepository('EyCBundle:NomencladorOp')->find($idnop);
        if ($nomop != NULL)
        {
            $em->remove($nomop);
            $em->flush();
            return 1;
        }
        else
             return 2;
//            throw new \InvalidArgumentException("La instancia de nomenclador con el id especificado no existe.");
    }



    /**
     * Mostrar una instancia de un nomenclador.
     *
     * @param int $idnomp  El id de la instancia del nomenclador que se quiere mostrar.
     *
     * @return array Un arreglo de dos dimensiones donde array['nom'] contiene la instancia del nomenclador y
     *         array['campos'] contiene los campos del nomenclador y sus valores.
     *
     *
     */

    public function mostrarNomOp($idnop,$order, $page, $limit, $filter,$cantCampos,$direction)
    {
        $nomop = array();
        $em = $this->doctrine->getManager();
        $nomop = $em->getRepository('EyCBundle:NomencladorOp')->findByMostrarValoresCamposNomOp($idnop, $order, $page, $limit, $filter,$cantCampos,$direction);

        if ($nomop != NULL)
        {
            return $nomop;
        }
    }


    public function mostrarNomOp2($idnop)
    {
        $nomop = array();
        $em = $this->doctrine->getManager();
        $nomop = $em->getRepository('EyCBundle:NomencladorOp')->findByMostrarValoresCamposNomOp2($idnop);

        if ($nomop != NULL)
        {
            return $nomop;
        }
    }

    /**
     * Muestra todas las instancia de un nomenclador.
     *
     * @param int $idn  El id del nomenclador que se quiere mostrar.
     *
     * @return array Un arreglo que en cada posición contiene un arreglo donde en array['nom'] contiene la
     *         instancia del nomenclador y array['campos'] contiene los campos del nomenclador y sus valores.
     *
     *
     *
     */

    public function mostrarNomOps($idn)
    {
            $em = $this->doctrine->getManager();
            $nomencladorops = $em->getRepository('EyCBundle:NomencladorOp')->findBy(array('nomenclador' => $idn));
            if ($nomencladorops != NULL)
            {
                return $nomencladorops;
            }
            else
                return array();

    }

    /**
     * Mostrar valores de una instancia de un nomenclador.
     *
     * @param int $idnop  El id de la instancia del nomenclador que se quiere mostrar.
     *
     * @return array Un arreglo que en la primera posisión array['nom'] contiene la instancia del nomenclador y
     *         array['campos'] contiene los campos del nomenclador y sus valores.
     *
     *
     */

    public function mostrarValoresCamposNomOp($idnop)
    {
        $em = $this->doctrine->getManager();
        $nomencladorop = $em->getRepository('EyCBundle:NomencladorOp')->find($idnop);

        if ($nomencladorop != NULL)
        {
            $camposvalores = $em->getRepository('EyCBundle:NomencladorOp')->findByMostrarValoresCamposNomOp($idnop);

            if ($camposvalores != NULL)
            {
              $resultado = array('nom' => $nomencladorop, 'campos' => $camposvalores);

              return $resultado;
            }
            else
                throw new \InvalidArgumentException("La instancia del nomenclador no posee valores.");
        }
        else
            throw new \InvalidArgumentException("La instancia del nomenclador con el id especificado no existe.");
    }

	
    /**
     * Mostrar una instancia de una estructura.
     *
     */

    public function mostrarNomeOp($id)
    {
        $em = $this->doctrine->getManager();
        $estrucop = $em->getRepository('EyCBundle:NomencladorOP')->find($id);
        $respuesta =  array();

        if ($estrucop != NULL)
        {
            $respuesta['idNOP'] = $estrucop->getId();
            $respuesta['nombreNOP'] = $estrucop->getNombre();
            return $respuesta;
        }
        else
            return false;
    }
	
    /**
     * Mostrar una instancia de una estructura.
     *
     */

    public function mostrarNomeOpsByNomenclador($id)
    {
        $em = $this->doctrine->getManager();
        $estrucop = $em->getRepository('EyCBundle:NomencladorOp')->findNomOpByNom($id);

        if ($estrucop != NULL)
        {
            return $estrucop;
        }
        else
            return false;
    }
}

/*********************************** termina la gestión de los nomenladoresOp **********************************/


	
	
