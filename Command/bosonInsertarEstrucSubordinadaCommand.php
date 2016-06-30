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


class bosonInsertarEstrucSubordinadaCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('boson:eyc:insertarEstrucSub')
            ->setDefinition(array(new InputArgument('id', InputArgument::REQUIRED, 'id de la estructura padre', null),
                                  new InputArgument('ids', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'ids de las estructuras subordinadas', null)))
            ->setDescription('Inserta las estructuras subordinadas a una estructura dada')
            ->setHelp('Inserta dado un id de una  estructura, las estructuras subordinadas: ids = [id1, id2,...,idn]');

    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $ids = $input->getArgument('ids');

        $contenedor = $this->getContainer();
        $contenedor->get('estructura')->insertarEstrucSub($id, $ids);

        $output->writeln('Las subordinaciones se adicionaron correctamente');

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(' ');
        $dialog = $this->getHelperSet()->get('dialog');
        $contenedor = $this->getContainer();
        $estrucids = array();


        $estrucs = $contenedor->get('estructura')->mostrarEstrucs();

        if (count($estrucs) > 0)
        {
            $output->writeln(' ');
            $output->writeln('Estructuras registradas');
            $output->writeln(' ');
            foreach ($estrucs as $estruc)
            {
                $output->writeln('id:' . $estruc->getId() . ',     ' . 'nombre:' . $estruc->getNombre());
                $estrucids[] = $estruc->getId();
            }

            $output->writeln(' ');
            $id = $dialog->askAndValidate($output, '<info>Ingrese el id de la estructura a la que se desea adicionar estructuras subordinadas:</info> ',
                function ($valor) use ($estrucids) {
                    if (false == in_array($valor, $estrucids)) {
                        throw new \InvalidArgumentException($valor . ' no es un id válido');
                    }
                    return $valor;
                }, false);

            $id = intval($id);

            $ids = array();
           do
           {
               $output->writeln(' ');
               $idsub = $dialog->askAndValidate($output, '<info>Ingrese el id de la estructura subordinada [Salir:Enter]:</info> ',
                   function ($valor) use ($estrucids, $id, $ids) {
                       if ( $valor != false && (false == in_array($valor, $estrucids) || $id == (integer)$valor || in_array($valor, $ids))) {
                           throw new \InvalidArgumentException($valor . ' no es un id válido');
                       }
                       return $valor;
                   }, false, false);

               is_bool($idsub)? : $ids[] = intval($idsub);
           }
           while($idsub);

            $salir = $dialog->askConfirmation($output, '<info>¿Desea adicionar las subordinaciones? (y, n)[</info><comment>y</comment><info>]:</info>', true);

            if ($salir && count($ids) > 0)
            {
                $input->setArgument('id', $id);
                $input->setArgument('ids', $ids);
            }
            else
               exit;
        }
        else
            throw new \InvalidArgumentException("No existen estructuras registradas.");
   }
}
