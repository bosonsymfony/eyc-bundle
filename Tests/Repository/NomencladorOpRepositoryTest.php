<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 14/05/15
 * Time: 14:29
 */

namespace UCI\Boson\EyCBundle\Tests\Repository;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use UCI\Boson\EyCBundle\Entity\CampoNom;

class NomencladorOpRepositoryTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->container = static::$kernel->getContainer();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine.orm.entity_manager');

        $this->loadNomencladores();

    }

    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @dataProvider data
     */
    public function testMostrarValoresCamposNomOp($nombre)
    {
        $nomOp = $this->em->getRepository('EyCBundle:NomencladorOp')->findOneBy(array('nombre' => $nombre));
        $nombrencladorOP = $this->em
            ->getRepository('EyCBundle:NomencladorOp')->findByMostrarValoresCamposNomOp($nomOp->getId());
        $this->assertCount(2, $nombrencladorOP);
        $this->assertTrue($nombrencladorOP[0]->getCampoNom() instanceof CampoNom);
    }


    function loadNomencladores()
    {
        $this->getContainer()->get('nomenclador')->insertarNom("Asignatura");
        $qb = $this->em->createQueryBuilder();
        $qb->select('a')->from('EyCBundle:Nomenclador','a')->where('a.nombre = :nombre')->setParameter('nombre','Asignatura');
        $ids = $qb->getQuery()->getArrayResult();
        $id = $ids[count($ids) - 1];
        $this->getContainer()->get('nomenclador')->insertarCampoNom($id, 'Cantidad de semestres', 'integer', 'Cantidad de semestres necesarios para impartirla', false);
        $this->getContainer()->get('nomenclador')->insertarCampoNom($id, 'Nombre', 'string', 'El nombre', false);

        /******* dsaadsd  *****/
//
        $CamposNom = $this->getContainer()->get('nomenclador')->mostrarCamposNom($id);

        $this->getContainer()->get('nomenclador')->insertarNomOp($id, 'Matemática1', array(array('id' => $CamposNom[0]->getId(), 'valor' => "1"), array('id' => $CamposNom[1]->getId(), 'valor' => 'Matemática I')));
        $this->getContainer()->get('nomenclador')->insertarNomOp($id, 'Matemática2', array(array('id' => $CamposNom[0]->getId(), 'valor' => "2"), array('id' => $CamposNom[1]->getId(), 'valor' => 'Matemática II')));
        $this->getContainer()->get('nomenclador')->insertarNomOp($id, 'Matemática3', array(array('id' => $CamposNom[0]->getId(), 'valor' => "2"), array('id' => $CamposNom[1]->getId(), 'valor' => 'Matemática III')));

    }

    public function data(){
        return array(
            array(
                'nombre' => 'Matemática1',
            ),
            array(
                'nombre' => 'Matemática2',
            ),
            array(
                'nombre' => 'Matemática3',
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        $nomencladores = $this->em->getRepository('EyCBundle:Nomenclador')->findBy(array('nombre' => 'Asignatura'));
        foreach ($nomencladores as $nom) {
            $this->getContainer()->get('nomenclador')->eliminarNom($nom->getId());
        }

    }

}
 