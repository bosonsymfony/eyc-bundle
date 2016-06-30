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


class bosonInsertarCampoNomCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('boson:eyc:insertarCampoNom')
            ->setDefinition(array(new InputArgument('id', InputArgument::REQUIRED, 'Id del nomenclador', null),
                                  new InputArgument('campo', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Campo del nomenclador', null)))
            ->setDescription('Inserta un campo de un nomenclador')
            ->setHelp('Inserta un nomenclador, debe definirle sus atributos:
                       campo = {nombre, tipo, descripción, vinculado, nomencladorVin}
                       nombre: Es el nombre del campo
                       tipo: Tipo de dato {"bool", "integer", "double", "string", "date"}
                       descripción: para que se usa el campo
                       vinculado: true o false si está vinculado con un nomenclador
                       nomencladorVin: id del nomenclador vinculado');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $campo = $input->getArgument('campo');

        if (count($campo) > 0)
        {
            $contenedor = $this->getContainer();

            $error  =  $contenedor->get('nomenclador')->insertarCampoNom($id, $campo['nombre'], $campo['tipo'],
                                                                  $campo['descripcion'], $campo['vinculado'],
                                                                  $campo['nomencladorVin']);
               if ($error instanceof ConstraintViolationListInterface)
               {
                 foreach($error as $elem )
                 {
                     $output->writeln($elem->getMessage());
                     $output->writeln($elem->getPropertyPath());

                 }
                 exit;
               }
            $output->writeln('<info>El campo se insertó satisfactoriamente.</info>');
        }
        else
        {
            $output->writeln('<info>Las datos no son correctos, vuelva a intentarlo.</info>');
        }

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(' ');
        $dialog = $this->getHelperSet()->get('dialog');

        $ids = array();

        $output->writeln(' ');

        $contenedor = $this->getContainer();
        $noms = $contenedor->get('nomenclador')->mostrarNoms();

        if (count($noms) > 0)
        {
            $output->writeln(' ');
            $output->writeln('Nomencladores registrados');
            $output->writeln(' ');

            foreach ($noms as $nom) {
                $output->writeln('id:' . $nom->getId() . ',     ' . 'nombre:' . $nom->getNombre());
                $ids[] = $nom->getId();
            }

            $output->writeln(' ');

            $idn = $dialog->askAndValidate($output, '<info>Ingrese el id del nomenclador al que desea agregarle un campo:</info> ',
                function ($valor) use ($ids) {
                    if (false == in_array($valor, $ids)) {
                        throw new \InvalidArgumentException($valor . ' no es un id válido');
                    }
                    return $valor;
                }, false);

            $idn = intval($idn);

            $output->writeln(' ');

            $output->writeln(array('<question>Insertar campo al nomenclador</question>', ' ',
                '<comment><info>campo</info> = {<info>nombre</info>, <info>tipo</info>, <info>descripcion</info>, <info>vinculado</info>, <info>nomencladorVin</info>} </comment>',
                '<comment><info>nombre:</info> Es el nombre del campo</comment>',
                '<comment><info>tipo:</info> Tipo de dato {<question>bool</question>, <question>integer</question>, <question>double</question>, <question>string</question>, <question>date"}</question>',
                '<comment><info>descripción:</info> para que se usa el campo</comment>',
                '<comment><info>vinculado:</info> true o false si está vinculado con un nomenclador</comment>',
                '<comment><info>nomencladorVin:</info> id del nomenclador vinculado</comment>'));

            $output->writeln(' ');
            $nombre = $dialog->ask($output, '<info>Nombre del campo:</info>', 'campo');

            $output->writeln(' ');
            $tipo = $dialog->askAndValidate($output, '<info>Tipo de dato:</info> ',
                function ($valor) {
                    $tipo = array("bool", "integer", "double", "string", "date");

                    if (false == in_array($valor, $tipo)) {
                        throw new \InvalidArgumentException($valor . ' no es un tipo válido');
                    }
                    return $valor;
                }, false, 'string');

            $output->writeln(' ');
            $descripcion = $dialog->ask($output, '<info>Descripción del campo:</info>', 'descripcion');

            $output->writeln(' ');
            $vinculado = $dialog->askConfirmation($output, '<info>Es un campo vinculado a algún nomenclador: (y, n)[</info><comment>n</comment><info>]:</info>', false);

            if ($vinculado) {
                if (count($noms) > 1) {
                    $output->writeln(' ');
                    $output->writeln('Nomencladores registrados');
                    $output->writeln(' ');

                    foreach ($noms as $nom) {
                        $output->writeln('id:' . $nom->getId() . ',     ' . 'nombre:' . $nom->getNombre());
                    }

                    $output->writeln(' ');
                    $nomencladorVin = $dialog->askAndValidate($output, '<info>Ingrese el id del nomenclador vinculado:</info> ',
                        function ($valor) use ($ids, $idn) {
                            if (intval($valor) == $idn || false == in_array($valor, $ids)) {
                                throw new \InvalidArgumentException($valor . ' no es un id válido');
                            }
                            return $valor;
                        }, false);

                    $nomencladorVin = intval($nomencladorVin);

                    $tipo = 'integer';
                } else {
                    $output->writeln(array('No existen nomencladores registrados', 'escoja salir y registrelos'));
                    $dialog->askConfirmation($output, '<info>¿Desea salir? (y, n)[</info><comment>y</comment><info>]:</info>', true);

                    exit;
                }
            } else {
                $nomencladorVin = 0;
            }

            $campo = array('nombre' => $nombre, 'tipo' => $tipo, 'descripcion' => $descripcion,
                'vinculado' => $vinculado, 'nomencladorVin' => $nomencladorVin);

            $output->writeln(' ');
            $insertarnom = $dialog->askConfirmation($output, '<info>¿Desea insertar el campo? (y, n)[</info><comment>y</comment><info>]:</info>', true);

            if ($insertarnom)
            {
                $input->setArgument('id', $idn);
                $input->setArgument('campo', $campo);
            }
        }
        else
            $output->writeln('No existen nomencladores a los que agregarles campos');
   }
}