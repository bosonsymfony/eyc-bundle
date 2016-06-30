<?php

namespace UCI\Boson\EyCBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CampoNom
 *
 * @ORM\Table(name="eyc_campo_nom")
 * @ORM\Entity(repositoryClass="UCI\Boson\EyCBundle\Entity\CampoNomRepository")
 */
class CampoNom
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
     * @ORM\ManyToOne(targetEntity = "Nomenclador", inversedBy = "campoNom", cascade = {"persist"})
     * @ORM\JoinColumn(name = "nomenclador", referencedColumnName = "id", onDelete="CASCADE")
     */
    private $nomenclador;

    /**
     *
     *
     * @ORM\OneToMany(targetEntity = "ValoresNom", mappedBy = "campoNom", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private $valoresNom;


    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo_dato", type="string", length=50)
     * @Assert\Type(type="string")
     * @Assert\NotBlank()
     * @Assert\Choice(choices = {"bool", "integer", "double", "string", "date"})
     */
    private $tipoDato;


    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=255)
     * @Assert\Type(type="string")
     */
    private $descripcion;


    /**
     * @var boolean
     *
     * @ORM\Column(name="vinculado", type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="bool")
     */
    private $vinculado;

    /**
     * @var integer
     *
     * @ORM\Column(name="nomencladorvin", type="integer")
     * @Assert\NotNull()
     * @Assert\Type(type="integer")
     */
    private $nomencladorVin;


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
     * @return CampoNom
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
     * Set tipoDato
     *
     * @param string $tipoDato
     * @return CampoNom
     */
    public function setTipoDato($tipoDato)
    {
        $this->tipoDato = $tipoDato;

        return $this;
    }

    /**
     * Get tipoDato
     *
     * @return string 
     */
    public function getTipoDato()
    {
        return $this->tipoDato;
    }



    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return CampoNom
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }



    /**
     * Set vinculado
     *
     * @param boolean $vinculado
     * @return CampoNom
     */
    public function setVinculado($vinculado)
    {
        $this->vinculado = $vinculado;

        return $this;
    }

    /**
     * Get vinculado
     *
     * @return boolean 
     */
    public function getVinculado()
    {
        return $this->vinculado;
    }

    /**
     * Set nomenclador
     *
     * @param integer $nomenclador
     * @return CampoNom
     */
    public function setNomencladorVin($nomenclador)
    {
        $this->nomencladorVin = $nomenclador;

        return $this;
    }

    /**
     * Get nomenclador
     *
     * @return integer 
     */
    public function getNomencladorVin()
    {
        return $this->nomencladorVin;
    }


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->valoresNom = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set nomenclador
     *
     * @param \UCI\Boson\EyCBundle\Entity\Nomenclador $nomenclador
     * @return CampoNom
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

    /**
     * Add valoresNom
     *
     * @param \UCI\Boson\EyCBundle\Entity\ValoresNom $valoresNom
     * @return CampoNom
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

    function __toString()
    {
        return $this->getNombre();
    }

}
