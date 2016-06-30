<?php

namespace UCI\Boson\EyCBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CampoEstruc
 *
 * @ORM\Table(name="eyc_campo_estruc")
 * @ORM\Entity(repositoryClass="UCI\Boson\EyCBundle\Entity\CampoEstrucRepository")
 */
class CampoEstruc
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
     * @ORM\ManyToOne(targetEntity = "UCI\Boson\EyCBundle\Entity\Estructura", inversedBy = "campoEstruc", cascade = {"persist"})
     * @ORM\JoinColumn(name = "estructura", referencedColumnName = "id", onDelete="CASCADE")
     */
    private $estructura;

    /**
     *
     *
      * @ORM\OneToMany(targetEntity = "UCI\Boson\EyCBundle\Entity\ValoresEstruc", mappedBy = "campoEstruc", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private $valoresEstruc;

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
     * @ORM\Column(name="nomenclador", type="integer")
     * @Assert\NotNull()
     * @Assert\Type(type="integer")
     */
    private $nomenclador;



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
     * @return CampoEstruc
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
     * @return CampoEstruc
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
     * @return CampoEstruc
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
     * @return CampoEstruc
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
     * @return CampoEstruc
     */
    public function setNomenclador($nomenclador)
    {
        $this->nomenclador = $nomenclador;

        return $this;
    }

    /**
     * Get nomenclador
     *
     * @return integer 
     */
    public function getNomenclador()
    {
        return $this->nomenclador;
    }


    /**
     * Set valoresEstruc
     *
     * @param string $valoresEstruc
     * @return CampoEstruc
     */
    public function setValoresEstruc($valoresEstruc)
    {
        $this->valoresEstruc = $valoresEstruc;

        return $this;
    }

    /**
     * Get valoresEstruc
     *
     * @return string 
     */
    public function getValoresEstruc()
    {
        return $this->valoresEstruc;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->valoresEstruc = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add valoresEstruc
     *
     * @param \UCI\Boson\EyCBundle\Entity\ValoresEstruc $valoresEstruc
     * @return CampoEstruc
     */
    public function addValoresEstruc(\UCI\Boson\EyCBundle\Entity\ValoresEstruc $valoresEstruc)
    {
        $this->valoresEstruc[] = $valoresEstruc;

        return $this;
    }

    /**
     * Remove valoresEstruc
     *
     * @param \UCI\Boson\EyCBundle\Entity\ValoresEstruc $valoresEstruc
     */
    public function removeValoresEstruc(\UCI\Boson\EyCBundle\Entity\ValoresEstruc $valoresEstruc)
    {
        $this->valoresEstruc->removeElement($valoresEstruc);
    }

    /**
     * Set estructura
     *
     * @param \UCI\Boson\EyCBundle\Entity\Estructura $estructura
     * @return CampoEstruc
     */
    public function setEstructura(\UCI\Boson\EyCBundle\Entity\Estructura $estructura = null)
    {
        $this->estructura = $estructura;

        return $this;
    }

    /**
     * Get estructura
     *
     * @return \UCI\Boson\EyCBundle\Entity\Estructura
     */
    public function getEstructura()
    {
        return $this->estructura;
    }
}
