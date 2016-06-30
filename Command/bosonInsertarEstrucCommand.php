<?php
/**
 * Created by PhpStorm.
 * User: orlando
 * Date: 3/13/15
 * Time: 9:24 a.m.
 */

// EyCBundle/Command/insertarEstrucCommand.php
namespace UCI\Boson\EyCBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Validator\ConstraintViolationListInterface;


class bosonInsertarEstrucCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('boson:eyc:insertarEstruc')
            ->setDefinition(array(new InputArgument('nombre', InputArgument::REQUIRED, 'Nombre de la estructura', null),
                                  new InputArgument('raiz', InputArgument::REQUIRED, 'Si puede ser el primer nodo de la jerarquia', null),
                                  new InputArgument('campos', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Campos de la estructura', null)))
            ->setDescription('Inserta una estructura')
            ->setHelp('Inserta una estructura, debe definirle sus atributos, nombre, si es raiz y sus campos:
                       campos = {[nombre, tipo, descripcion, vinculado, nomenclador],.....,[]}
                       nombre: Es el nombre del campo
                       tipo: Tipo de dato {"bool", "integer", "double", "string", "date"}
                       descripción: para que se usa el campo
                       vinculado: true o false si está vinculado con un nomenclador
                       nomenclador: id del nomenclador vinculado');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nombre = $input->getArgument('nombre');
        $raiz = $input->getArgument('raiz');
        $campos = $input->getArgument('campos');

        if ($nombre != " " && is_bool($raiz) && count($campos) > 0)
        {
            $contenedor = $this->getContainer();
            $idestruc = $contenedor->get('estructura')->insertarEstruc($nombre, $raiz);

            $output->writeln($idestruc.'  Este es el ID de la estructura insertada');

            foreach ($campos as $campo)
            {
              $error  =  $contenedor->get('estructura')->insertarCampoEstruc($idestruc, $campo['nombre'], $campo['tipo'],
                                                                  $campo['descripcion'], $campo['vinculado'],
                                                                  $campo['nomenclador']);
               if ($error instanceof ConstraintViolationListInterface)
               {
                 foreach($error as $elem )
                 {
                     $output->writeln($elem->getMessage());
                     $output->writeln($elem->getPropertyPath());


                 }
                 $contenedor->get('estructura')->eliminarEstruc($idestruc);

                 return ;
               }
            }
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
        $nombreEstruc = $dialog->ask($output, '<info>Nombre de la estructura:</info>', 'estructura');
        $output->writeln(' ');
        $raiz = $dialog->askConfirmation($output, '<info>¿Es raiz la estructura? (y, n)[</info><comment>y</comment><info>]:</info>', true);
        $campos = array();

        $output->writeln(' ');
        $insertarcampos = $dialog->askConfirmation($output, '<info>¿Desea insertarle campos a la estructura? (y, n)[</info><comment>y</comment><info>]:</info>', true);

        if ($insertarcampos)
        {
            $output->writeln(' ');
            $output->writeln(array('<question>Insertar campos de la estructura</question>', ' ',
                '<comment><info>campos</info> = {[<info>nombre</info>, <info>tipo</info>, <info>descripción</info>, <info>vinculado</info>, <info>nomenclador</info>],.....,[]} </comment>',
                '<comment><info>nombre:</info> Es el nombre del campo</comment>',
                '<comment><info>tipo:</info> Tipo de dato {<question>bool</question>, <question>integer</question>, <question>double</question>, <question>string</question>, <question>date</question>}',
                '<comment><info>descripción:</info> para que se usa el campo</comment>',
                '<comment><info>vinculado:</info> true o false si está vinculado con un nomenclador</comment>',
                '<comment><info>nomenclador:</info> id del nomenclador vinculado</comment>'));

            do {
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
                $descripcion = $dialog->ask($output, '<info>Descripción del campo:</info>', 'descripción');

                $output->writeln(' ');
                $vinculado = $dialog->askConfirmation($output, '<info>Es un campo vinculado a nomenclador: (y, n)[</info><comment>n</comment><info>]:</info>', false);

                if ($vinculado)
                {

                    $ids = array();
                    $contenedor = $this->getContainer();
                    $noms = $contenedor->get('nomenclador')->mostrarNoms();

                    if (count($noms) > 0)
                    {
                        $tipo = 'integer';
                        $output->writeln(' ');
                        $output->writeln('Nomencladores registrados');
                        $output->writeln(' ');
                        foreach ($noms as $nom)
                        {
                            $output->writeln('id:' . $nom->getId() . ',     ' . 'nombre:' . $nom->getNombre());
                            $ids[] = $nom->getId();
                        }

                        $output->writeln(' ');
                        $nomenclador = $dialog->askAndValidate($output, '<info>Ingrese el id del nomenclador vinculado:</info> ',
                            function ($valor) use ($ids) {
                                if (false == in_array($valor, $ids)) {
                                    throw new \InvalidArgumentException($valor . ' no es un id válido');
                                }
                                return $valor;
                            }, false);

                        $nomenclador = intval($nomenclador);
                    }
                    else
                    {
                        $output->writeln(array('No existen nomencladores registrados','escoja salir y registrelos','de lo contrario el campo no estará vinculado a ningun nomenclador'));
                        $salir = $dialog->askConfirmation($output, '<info>¿Desea salir? (y, n)[</info><comment>y</comment><info>]:</info>', true);

                        if ($salir)
                        {
                          return;
                        }
                        else
                        {
                            $nomenclador = 0;
                            $vinculado = false;

                        }
                    }

                } else
                {
                    $nomenclador = 0;
                }
                $campo = array('nombre' => $nombre, 'tipo' => $tipo, 'descripcion' => $descripcion,
                               'vinculado' => $vinculado, 'nomenclador' => $nomenclador);

                $campos[] = $campo;

                $output->writeln(' ');
                $control = $dialog->askConfirmation($output, '<info>¿Desea adicionar un nuevo campo? (y, n)[</info><comment>y</comment><info>]:</info>', true);

            } while ($control);

            $output->writeln(' ');
            $insertarestruc = $dialog->askConfirmation($output, '<info>¿Desea insertar la estructura? (y, n)[</info><comment>y</comment><info>]:</info>', true);

            if ($insertarestruc)
            {
                $input->setArgument('nombre', $nombreEstruc);
                $input->setArgument('raiz', $raiz);
                $input->setArgument('campos', $campos);
            }
            else
                exit;

        }
        else
        {
            $output->writeln(array('No se puede insertar una estructura sino define sus campos'));
            exit;
        }
   }

}
