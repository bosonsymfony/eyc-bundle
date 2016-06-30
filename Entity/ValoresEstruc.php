<?php

namespace UCI\Boson\EyCBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ValoresEstruc
 *
 * @ORM\Table(name="eyc_valores_estruc")
 * @ORM\Entity(repositoryClass="UCI\Boson\EyCBundle\Entity\ValoresEstrucRepository")
 */
class ValoresEstruc
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
     * @ORM\ManyToOne(targetEntity = "EstructuraOp", inversedBy = "valoresEstruc", cascade = {"persist"})
     * @ORM\JoinColumn(name = "estructuraOp_id", referencedColumnName = "id", onDelete="CASCADE")
     */
    private $estructuraOp;

    /**
     *
     *
     * @ORM\ManyToOne(targetEntity = "CampoEstruc", inversedBy = "valoresEstruc", cascade = {"persist"})
     * @ORM\JoinColumn(name = "campoEstruc", referencedColumnName = "id", onDelete="CASCADE")
     */
    private $campoEstruc;

    /**
     * @var string
     *
     * @ORM\Column(name="valor", type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $valor;


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
     * Set valor
     *
     * @param string $valor
     * @return ValoresEstruc
     */
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * Get valor
     *
     * @return string 
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * Set estructuraOp
     *
     * @param string $estructuraOp
     * @return ValoresEstruc
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
     * Set campoEstruc
     *
     * @param CampoEstruc $campoEstruc
     * @return ValoresEstruc
     */
    public function setCampoEstruc(\UCI\Boson\EyCBundle\Entity\CampoEstruc $campoEstruc)
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
}
