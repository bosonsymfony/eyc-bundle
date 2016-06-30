<?php

namespace UCI\Boson\EyCBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * NomencladorOp
 *
 * @ORM\Table(name="eyc_nomenclador_op")
 * @ORM\Entity(repositoryClass="UCI\Boson\EyCBundle\Entity\NomencladorOpRepository")
 */
class NomencladorOp
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
     * @ORM\OneToMany(targetEntity = "ValoresNom", mappedBy = "nomencladorOp", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private $valoresNom;

    /**
     *
     *
     * @ORM\ManyToOne(targetEntity = "Nomenclador", inversedBy = "nomencladorOp", cascade = {"persist"})
     * @ORM\JoinColumn(name = "nomenclador", referencedColumnName = "id", onDelete="CASCADE")
     *
     */
    private $nomenclador;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
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
     * @return NomencladorOp
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
        $this->valoresNom = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add valoresNom
     *
     * @param \UCI\Boson\EyCBundle\Entity\ValoresNom $valoresNom
     * @return NomencladorOp
     */
    public function addValoresNom(\UCI\Boson\EyCBundle\Entity\ValoresNom $valoresNom)
    {
        $this->valoresNom[] = $valoresNom;

        return $this;
    }

    /**
     * Remove valoresNom
     *
     * @param \UCI\Boson\EyCBundle\Entity\ValoresNom $valoresNom
     */
    public function removeValoresNom(\UCI\Boson\EyCBundle\Entity\ValoresNom $valoresNom)
    {
        $this->valoresNom->removeElement($valoresNom);
    }

    /**
     * Get valoresNom
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getValoresNom()
    {
        return $this->valoresNom;
    }

    /**
     * Set nomenclador
     *
     * @param \UCI\Boson\EyCBundle\Entity\Nomenclador $nomenclador
     * @return NomencladorOp
     */
    public function setNomenclador(\UCI\Boson\EyCBundle\Entity\Nomenclador $nomenclador = null)
    {
        $this->nomenclador = $nomenclador;

        return $this;
    }

    /**
     * Get nomenclador
     *
     * @return \UCI\Boson\EyCBundle\Entity\Nomenclador
     */
    public function getNomenclador()
    {
        return $this->nomenclador;
    }

    function __toString()
    {
        return $this->nombre;
    }


}
