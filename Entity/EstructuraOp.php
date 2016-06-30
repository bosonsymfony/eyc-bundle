<?php

namespace UCI\Boson\EyCBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * EstructuraOp
 *
 * @ORM\Table(name="eyc_estructura_op")
 * @ORM\Entity(repositoryClass="UCI\Boson\EyCBundle\Entity\EstructuraOpRepository")
 */
class EstructuraOp
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
     * @ORM\OneToMany(targetEntity="EstructuraOp", mappedBy="parent")
     *
     */
    private $children;


    /**
     *
     * @ORM\ManyToOne(targetEntity="EstructuraOp", inversedBy="children")
     * @ORM\JoinColumn(name="parent", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     *
     *
     * @ORM\ManyToOne(targetEntity = "Estructura", inversedBy = "estructuraOp", cascade = {"persist"})
     * @ORM\JoinColumn(name = "estructura", referencedColumnName = "id", onDelete="CASCADE")
     */
    private $estructura;

    /**
     *
     *
     * @ORM\OneToMany(targetEntity = "ValoresEstruc", mappedBy = "estructuraOp", cascade={"remove", "persist"}, orphanRemoval=true)
     */
    private $valoresEstruc;

    /**
     *
     *
     * @ORM\ManyToMany(targetEntity = "Dominio", inversedBy = "estructuraOp")
     * @ORM\JoinTable(name = "eyc_dominio_estructura_op",
     *                joinColumns = {@ORM\JoinColumn(name = "estructuraOp_id", referencedColumnName = "id")},
     *                inverseJoinColumns = {@ORM\JoinColumn(name = "dominio_id", referencedColumnName = "id")})
     *
     */
    private $dominio;


    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $nombre;




    public function __construct() {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->dominio = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return EstructuraOp
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
     * Set children
     *
     * @param \UCI\Boson\EyCBundle\Entity\EstructuraOP $children
     * @return EstructuraOp
     *
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Get children
     *
     * @return EstructuraOp
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set padre
     *
     * @param \UCI\Boson\EyCBundle\Entity\EstructuraOP $parent
     * @return EstructuraOp
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }


    /**
     * Get parent
     *
     * @return EstructuraOp
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set estructura
     *
     * @param \UCI\Boson\EyCBundle\Entity\Estructura $estructura
     * @return EstructuraOp
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

    /**
     * Set valoresEstruc
     *
     * @param string $valoresEstruc
     * @return EstructuraOp
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
     * Set dominio
     *
     * @param string $dominio
     * @return EstructuraOp
     */
    public function setDominio($dominio)
    {
        $this->dominio = $dominio;

        return $this;
    }

    /**
     * Get dominio
     *
     * @return string 
     */
    public function getDominio()
    {
        return $this->dominio;
    }

    /**
     * Add hijos
     *
     * @param \UCI\Boson\EyCBundle\Entity\EstructuraOp $children
     * @return EstructuraOp
     */
    public function addChildren(\UCI\Boson\EyCBundle\Entity\EstructuraOp $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove hijos
     *
     * @param \UCI\Boson\EyCBundle\Entity\EstructuraOp $hijos
     */
    public function removeChildren(\UCI\Boson\EyCBundle\Entity\EstructuraOp $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Add valoresEstruc
     *
     * @param \UCI\Boson\EyCBundle\Entity\ValoresEstruc $valoresEstruc
     * @return EstructuraOp
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
     * Add dominio
     *
     * @param \UCI\Boson\EyCBundle\Entity\Dominio $dominio
     * @return EstructuraOp
     */
    public function addDominio(\UCI\Boson\EyCBundle\Entity\Dominio $dominio)
    {
        $this->dominio[] = $dominio;

        return $this;
    }

    /**
     * Remove dominio
     *
     * @param \UCI\Boson\EyCBundle\Entity\Dominio $dominio
     */
    public function removeDominio(\UCI\Boson\EyCBundle\Entity\Dominio $dominio)
    {
        $this->dominio->removeElement($dominio);
    }

    /**
     * Add children
     *
     * @param \UCI\Boson\EyCBundle\Entity\EstructuraOp $children
     * @return EstructuraOp
     */
    public function addChild(\UCI\Boson\EyCBundle\Entity\EstructuraOp $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \UCI\Boson\EyCBundle\Entity\EstructuraOp $children
     */
    public function removeChild(\UCI\Boson\EyCBundle\Entity\EstructuraOp $children)
    {
        $this->children->removeElement($children);
    }
}
