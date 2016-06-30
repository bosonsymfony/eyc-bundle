<?php
/**
 * Created by PhpStorm.
 * User: orlando
 * Date: 3/13/15
 * Time: 9:24 a.m.
 */

// EyCBundle/Command/valoresNomCommand.php
namespace UCI\Boson\EyCBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Validator\ConstraintViolationListInterface;


class bosonInsertarNomOpCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('boson:eyc:insertarNomOp')
            ->setDefinition(array(new InputArgument('id', InputArgument::REQUIRED, 'Id del nomenclador', null),
                                  new InputArgument('nombre', InputArgument::REQUIRED, 'Nombre de la instancia del nomenclador', null),
                                  new InputArgument('campos', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Campos de la instancia del nomenclador', null)))
            ->setDescription('Inserta una instancia de un nomenclador')
            ->setHelp('Inserta una instancia de un nomenclador, cuyos atributos están en correspondencia
                       con los campos definidos para el nomenclador especificado con el id.');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $nombre = $input->getArgument('nombre');
        $campos = $input->getArgument('campos');

        if ($id && $nombre != " " && count($campos) > 0)
        {
            $contenedor = $this->getContainer();
            $error = $contenedor->get('nomenclador')->insertarNomOp($id, $nombre, $campos);
            if ($error instanceof ConstraintViolationListInterface)
            {
                foreach ($error as $elem)
                {
                    $output->writeln($elem->getMessage());
                    $output->writeln($elem->getPropertyPath());
                }
            }
            else
            {
                $output->writeln('<info>La instancia del nomenclador se insertó satisfactoriamente.</info>');
            }
        }
        else
            $output->writeln('<info>Las datos no son correctos, vuelva a intentarlo.</info>');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(' ');
        $contenedor = $this->getContainer();
        $dialog = $this->getHelperSet()->get('dialog');

        $ids = array();
        $nombrenomenclador = array();
        $valorescampos = array();
        $noms = $contenedor->get('nomenclador')->mostrarNoms();

        if (count($noms) > 0) {
            $output->writeln(' ');
            $output->writeln('Nomencladores registrados');
            $output->writeln(' ');

            foreach ($noms as $nom) {
                $output->writeln('id:' . $nom->getId() . ',     ' . 'nombre:' . $nom->getNombre());
                $ids[] = $nom->getId();
                $nombrenomenclador[] = $nom->getNombre();
            }

            $nomgeristrados = array_combine($ids, $nombrenomenclador);

            $output->writeln(' ');
            $nomenclador = $dialog->askAndValidate($output, '<info>Ingrese el id del nomenclador que desea instanciar:</info> ',
                function ($valor) use ($ids) {
                    if (false == in_array($valor, $ids)) {
                        throw new \InvalidArgumentException($valor . ' no es un id válido');
                    }
                    return $valor;
                }, false);

            $nomenclador = intval($nomenclador);

            $output->writeln(' ');

            $camposnomenclador = $contenedor->get('nomenclador')->mostrarCamposNom($nomenclador);

            if (count($camposnomenclador) > 0)
            {
                $nombre = $dialog->ask($output, '<info>Instancia del nomenclador </info>' . $nomgeristrados[$nomenclador] . '<info>:</info>', 'nomencladorop');

                foreach ($camposnomenclador as $campo)
                {
                    $output->writeln(' ');
                    if ($campo->getVinculado())
                    {
                        $nomencladorops = $contenedor->get('nomenclador')->mostrarNomOps($campo->getNomencladorVin());

                        if (count($nomencladorops) > 0)
                        {
                            $idsop = array();

                            $output->writeln(' ');
                            $output->writeln('Instancias de nomencladores registradas');
                            $output->writeln(' ');

                            foreach ($nomencladorops as $nomop) {
                                $output->writeln('id:' . $nomop->getId() . ',     ' . 'nombre:' . $nomop->getNombre());
                                $idsop[] = $nomop->getId();
                            }

                            $output->writeln(' ');
                            $nomencladorop = $dialog->askAndValidate($output, '<info>Ingrese el id de la instancia del nomenclador:</info> ',
                                function ($valor) use ($idsop) {
                                    if (false == in_array($valor, $idsop)) {
                                        throw new \InvalidArgumentException($valor . ' no es un id válido');
                                    }
                                    return $valor;
                                }, false);


                            $valorescampos[] = array('id' => $campo->getId(), 'valor' => $nomencladorop);
                        }
                        else
                        {
                            $output->writeln(array('No existen instancias de nomencladores registrados', 'escoja salir y regístrelos'));
                            $salir = $dialog->askConfirmation($output, '<info>¿Desea salir? (y, n)[</info><comment>y</comment><info>]:</info>', true);

                            exit;
                        }
                    }
                    else
                    {
                        $output->writeln(' ');
                        $valorentrada = $dialog->askAndValidate($output, '<info>Ingrese </info>' . $campo->getNombre() . ' ' . $nombre . '<info>:</info> ',
                            function ($valor) use ($contenedor, $campo) {

                                if (!$contenedor->get('nomenclador')->validarTipoValor($campo->getTipoDato(), $valor)) {
                                    throw new \InvalidArgumentException($valor . ' no es un id válido');
                                }
                                return $valor;
                            }, false);


                        $valorescampos[] = array('id' => $campo->getId(), 'valor' => $valorentrada);

                    }

                }

                $output->writeln(' ');
                $insertarnom = $dialog->askConfirmation($output, '<info>¿Desea insertar el nomenclador? (y, n)[</info><comment>y</comment><info>]:</info>', true);

                if ($insertarnom) {
                    $input->setArgument('id', $nomenclador);
                    $input->setArgument('nombre', $nombre);
                    $input->setArgument('campos', $valorescampos);
                }

          }
          else
              $output->writeln(array('No existen campos registrados para este nomenclador'));
        }
        else
        {
            $output->writeln(array('No existen nomencladores registrados','escoja salir y regístrelos'));

        }
    }
}
