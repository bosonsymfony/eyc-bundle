<?php

namespace UCI\Boson\EyCBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use UCI\Boson\EyCBundle\Entity\NomencladorOp;
use UCI\Boson\EyCBundle\Entity\CampoNom;
/**
 * Nomenclador
 *
 * @ORM\Table(name="eyc_nomenclador")
 * @ORM\Entity(repositoryClass="UCI\Boson\EyCBundle\Entity\NomencladorRepository")
 */
class Nomenclador
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     *
     * @ORM\OneToMany(targetEntity = "UCI\Boson\EyCBundle\Entity\NomencladorOp", mappedBy = "nomenclador", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private $nomencladorOp;

    /**
     *
     *
     * @ORM\OneToMany(targetEntity = "UCI\Boson\EyCBundle\Entity\CampoNom", mappedBy = "nomenclador", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private $campoNom;
    
    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=255)
     * @Assert\Type(type="string")
     * @Assert\NotBlank()
     */
    private $nombre;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return Nomenclador
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->nomencladorOp = new \Doctrine\Common\Collections\ArrayCollection();
        $this->campoNom = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add nomencladorOp
     *
     * @param \UCI\Boson\EyCBundle\Entity\NomencladorOp $nomencladorOp
     * @return Nomenclador
     */
    public function addNomencladorOp(\UCI\Boson\EyCBundle\Entity\NomencladorOp $nomencladorOp)
    {
        $this->nomencladorOp[] = $nomencladorOp;

        return $this;
    }

    /**
     * Remove nomencladorOp
     *
     * @param \UCI\Boson\EyCBundle\Entity\NomencladorOp $nomencladorOp
     */
    public function removeNomencladorOp(\UCI\Boson\EyCBundle\Entity\NomencladorOp $nomencladorOp)
    {
        $this->nomencladorOp->removeElement($nomencladorOp);
    }

    /**
     * Get nomencladorOp
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNomencladorOp()
    {
        return $this->nomencladorOp;
    }

    /**
     * Add campoNom
     *
     * @param \UCI\Boson\EyCBundle\Entity\CampoNom $campoNom
     * @return Nomenclador
     */
    public function addCampoNom(\UCI\Boson\EyCBundle\Entity\CampoNom $campoNom)
    {
        $this->campoNom[] = $campoNom;

        return $this;
    }

    /**
     * Remove campoNom
     *
     * @param \UCI\Boson\EyCBundle\Entity\CampoNom $campoNom
     */
    public function removeCampoNom(\UCI\Boson\EyCBundle\Entity\CampoNom $campoNom)
    {
        $this->campoNom->removeElement($campoNom);
    }

    /**
     * Get campoNom
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCampoNom()
    {
        return $this->campoNom;
    }
}
