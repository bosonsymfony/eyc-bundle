<?php

namespace UCI\Boson\EyCBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dominio
 *
 * @ORM\Table(name="eyc_dominio")
 * @ORM\Entity(repositoryClass="UCI\Boson\EyCBundle\Entity\DominioRepository")
 */
class Dominio
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
     * @ORM\OneToMany(targetEntity="Dominio", mappedBy="padre")
     */
    private $hijos;

    /**
     *
     *
     * @ORM\ManyToOne(targetEntity="Dominio", inversedBy="hijos")
     * @ORM\JoinColumn(name="padre", referencedColumnName="id")
     */
    private $padre;

    /**
     *
     *
     * @ORM\ManyToMany(targetEntity = "EstructuraOp", mappedBy = "dominio")
     */
    private $estructuraOp;


    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=255)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="string", length=255)
     */
    private $descripcion;

    public function __construct() {
        $this->estructuraOp = new \Doctrine\Common\Collections\ArrayCollection();
    }


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
     * @return Dominio
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
     * Set descripcion
     *
     * @param string $descripcion
     * @return Dominio
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
     * Set idPadre
     *
     * @param integer $idPadre
     * @return Dominio
     */
    public function setIdPadre($idPadre)
    {
        $this->idPadre = $idPadre;

        return $this;
    }

    /**
     * Get idPadre
     *
     * @return integer 
     */
    public function getIdPadre()
    {
        return $this->idPadre;
    }

    /**
     * Set estructuraOp
     *
     * @param string $estructuraOp
     * @return Dominio
     */
    public function setEstructuraOp($estructuraOp)
    {
        $this->estructuraOp = $estructuraOp;

        return $this;
    }

    /**
     * Get estructuraOp
     *
     * @return string 
     */
    public function getEstructuraOp()
    {
        return $this->estructuraOp;
    }

    /**
     * Add estructuraOp
     *
     * @param \UCI\Boson\EyCBundle\Entity\EstructuraOp $estructuraOp
     * @return Dominio
     */
    public function addEstructuraOp(\UCI\Boson\EyCBundle\Entity\EstructuraOp $estructuraOp)
    {
        $this->estructuraOp[] = $estructuraOp;

        return $this;
    }

    /**
     * Remove estructuraOp
     *
     * @param \UCI\Boson\EyCBundle\Entity\EstructuraOp $estructuraOp
     */
    public function removeEstructuraOp(\UCI\Boson\EyCBundle\Entity\EstructuraOp $estructuraOp)
    {
        $this->estructuraOp->removeElement($estructuraOp);
    }

    /**
     * Add hijos
     *
     * @param \UCI\Boson\EyCBundle\Entity\Dominio $hijos
     * @return Dominio
     */
    public function addHijo(\UCI\Boson\EyCBundle\Entity\Dominio $hijos)
    {
        $this->hijos[] = $hijos;

        return $this;
    }

    /**
     * Remove hijos
     *
     * @param \UCI\Boson\EyCBundle\Entity\Dominio $hijos
     */
    public function removeHijo(\UCI\Boson\EyCBundle\Entity\Dominio $hijos)
    {
        $this->hijos->removeElement($hijos);
    }

    /**
     * Get hijos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getHijos()
    {
        return $this->hijos;
    }

    /**
     * Set padre
     *
     * @param \UCI\Boson\EyCBundle\Entity\Dominio $padre
     * @return Dominio
     */
    public function setPadre(\UCI\Boson\EyCBundle\Entity\Dominio $padre = null)
    {
        $this->padre = $padre;

        return $this;
    }

    /**
     * Get padre
     *
     * @return \UCI\Boson\EyCBundle\Entity\Dominio
     */
    public function getPadre()
    {
        return $this->padre;
    }
}
