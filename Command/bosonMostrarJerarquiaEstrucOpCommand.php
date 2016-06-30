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



class bosonMostrarJerarquiaEstrucOpCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('boson:eyc:mostrarJerarquiaEstrucOp')
            ->setDescription('Muestra la Jerarquía de estructuras')
            ->setHelp('Muestra la Jerarquía de estructuras');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contenedor = $this->getContainer();
        $estruc = $contenedor->get('estructura')->mostrarEstrucRaizOp();
        $jerarquia = "";

        if ($estruc != NULL)
        {
            foreach( $estruc as $est)
            {
              $this->mostrarJerarquia($output, $est, $jerarquia);
            }

        }
        $output->write($jerarquia);
    }



    protected function mostrarJerarquia(OutputInterface $output, $nodo, &$jerarquia, $separador = " ")
    {
        if ($nodo != NULL)
        {
            $jerarquia .= $separador.strval($nodo->getId())."   ".$nodo->getNombre()."\n";

            $hijos = $nodo -> getChildren();

            foreach ($hijos as $h)
            {
               if ($nodo != $h)
               {

                   $this->mostrarJerarquia($output, $h, $jerarquia, $separador." ");
               }
            }

        }
    }
}
