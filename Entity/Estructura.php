<?php

namespace UCI\Boson\EyCBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Estructura
 *
 * @ORM\Table(name="eyc_estructura")
 * @ORM\Entity(repositoryClass="UCI\Boson\EyCBundle\Entity\EstructuraRepository")
 */
class Estructura
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
     * @ORM\ManyToMany(targetEntity="Estructura", mappedBy="estructurasHijas")
     **/
    private $estructurasPadres;

    /**
     * @ORM\ManyToMany(targetEntity="Estructura", inversedBy="estructurasPadres")
     * @ORM\JoinTable(name="eyc_estructuras_relacionadas",
     *            joinColumns={@ORM\JoinColumn(name="estruc_pa", referencedColumnName="id")},
     *           inverseJoinColumns={@ORM\JoinColumn(name="estruc_hi", referencedColumnName="id")})
     **/
    private $estructurasHijas;

    /**
     *
     *
     * @ORM\OneToMany(targetEntity = "CampoEstruc", mappedBy = "estructura", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private $campoEstruc;

    /**
     *
     *
     * @ORM\OneToMany(targetEntity = "EstructuraOp", mappedBy = "estructura", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private $estructuraOp;


    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=255)
     * @Assert\Type(type="string")
     * @Assert\NotBlank()
     */
    private $nombre;

    /**
     * @var boolean
     *
     * @ORM\Column(name="raiz", type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="bool")
     */
    private $raiz;


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
     * @return Estructura
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
     * Set raiz
     *
     * @param boolean $raiz
     * @return Estructura
     */
    public function setRaiz($raiz)
    {
        $this->raiz = $raiz;

        return $this;
    }

    /**
     * Get raiz
     *
     * @return boolean 
     */
    public function getRaiz()
    {
        return $this->raiz;
    }



    /**
     * Set campoEstruc
     *
     * @param \UCI\Boson\EyCBundle\Entity\CampoEstruc $campoEstruc
     * @return Estructura
     */
    public function setCampoEstruc(\UCI\Boson\EyCBundle\Entity\CampoEstruc $campoEstruc  = NULL)
    {
        $this->campoEstruc = $campoEstruc;

        return $this;
    }

    /**
     * Get campoEstruc
     *
     * @return string 
     */
    public function getCampoEstruc()
    {
        return $this->campoEstruc;
    }

    /**
     * Set estructuraOp
     *
     * @param \UCI\Boson\EyCBundle\Entity\EstructuraOp $estructuraOp
     * @return Estructura
     */
    public function setEstructuraOp(\UCI\Boson\EyCBundle\Entity\EstructuraOp $estructuraOp)
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
     * Add campoEstruc
     *
     * @param \UCI\Boson\EyCBundle\Entity\CampoEstruc $campoEstruc
     * @return Estructura
     */
    public function addCampoEstruc(\UCI\Boson\EyCBundle\Entity\CampoEstruc $campoEstruc)
    {
        $this->campoEstruc[] = $campoEstruc;

        return $this;
    }

    /**
     * Remove campoEstruc
     *
     * @param \UCI\Boson\EyCBundle\Entity\CampoEstruc $campoEstruc
     */
    public function removeCampoEstruc(\UCI\Boson\EyCBundle\Entity\CampoEstruc $campoEstruc)
    {
        $this->campoEstruc->removeElement($campoEstruc);
    }

    /**
     * Add estructuraOp
     *
     * @param \UCI\Boson\EyCBundle\Entity\EstructuraOp $estructuraOp
     * @return Estructura
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
     * Constructor
     */
    public function __construct()
    {
        $this->estructurasPadres = new \Doctrine\Common\Collections\ArrayCollection();
        $this->estructurasHijas = new \Doctrine\Common\Collections\ArrayCollection();
        $this->campoEstruc = new \Doctrine\Common\Collections\ArrayCollection();
        $this->estructuraOp = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add estructurasPadres
     *
     * @param \UCI\Boson\EyCBundle\Entity\Estructura $estructurasPadres
     * @return Estructura
     */
    public function addEstructurasPadre(\UCI\Boson\EyCBundle\Entity\Estructura $estructurasPadres)
    {
        $this->estructurasPadres[] = $estructurasPadres;

        return $this;
    }

    /**
     * Remove estructurasPadres
     *
     * @param \UCI\Boson\EyCBundle\Entity\Estructura $estructurasPadres
     */
    public function removeEstructurasPadre(\UCI\Boson\EyCBundle\Entity\Estructura $estructurasPadres)
    {
        $this->estructurasPadres->removeElement($estructurasPadres);

        if (!$this->estructurasPadres->contains($estructurasPadres)) {
            return;
        }
        $this->estructurasPadres->removeElement($estructurasPadres);
        $estructurasPadres->removeEstructurasHija($this);
    }


    /**
     * Get estructurasPadres
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEstructurasPadres()
    {
        return $this->estructurasPadres;
    }

    /**
     * Add estructurasHijas
     *
     * @param \UCI\Boson\EyCBundle\Entity\Estructura $estructurasHijas
     * @return Estructura
     */
    public function addEstructurasHija(\UCI\Boson\EyCBundle\Entity\Estructura $estructurasHijas)
    {
        $this->estructurasHijas[] = $estructurasHijas;

        return $this;
    }

    /**
     * Remove estructurasHijas
     *
     * @param \UCI\Boson\EyCBundle\Entity\Estructura $estructurasHijas
     */
    public function removeEstructurasHija(\UCI\Boson\EyCBundle\Entity\Estructura $estructurasHijas)
    {
        if (!$this->estructurasHijas->contains($estructurasHijas)) {
            return;
        }
        $this->estructurasHijas->removeElement($estructurasHijas);
        $estructurasHijas->removeEstructurasPadre($this);

    }

    /**
     * Get estructurasHijas
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEstructurasHijas()
    {
        return $this->estructurasHijas->toArray();
    }
}
