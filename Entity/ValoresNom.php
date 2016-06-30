<?php

namespace UCI\Boson\EyCBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ValoresNom
 *
 * @ORM\Table(name="eyc_valores_nom")
 * @ORM\Entity(repositoryClass="UCI\Boson\EyCBundle\Entity\ValoresNomRepository")
 */
class ValoresNom
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
     * @ORM\ManyToOne(targetEntity = "NomencladorOp", inversedBy = "valoresNom", cascade = {"persist"})
     * @ORM\JoinColumn(name = "nomencladorOp", referencedColumnName = "id", onDelete="CASCADE")
     */
    private $nomencladorOp;


    /**
     *
     *
     * @ORM\ManyToOne(targetEntity = "CampoNom", inversedBy = "valoresNom",  cascade = {"persist"})
     * @ORM\JoinColumn(name = "campoNom", referencedColumnName = "id", onDelete="CASCADE")
     */
    private $campoNom;


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
     * @return ValoresNom
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
     * Set nomencladorOp
     *
     * @param \UCI\Boson\EyCBundle\Entity\NomencladorOp $nomencladorOp
     * @return ValoresNom
     */
    public function setNomencladorOp(\UCI\Boson\EyCBundle\Entity\NomencladorOp $nomencladorOp = null)
    {
        $this->nomencladorOp = $nomencladorOp;

        return $this;
    }

    /**
     * Get nomencladorOp
     *
     * @return \UCI\Boson\EyCBundle\Entity\NomencladorOp
     */
    public function getNomencladorOp()
    {
        return $this->nomencladorOp;
    }

    /**
     * Set campoNom
     *
     * @param \UCI\Boson\EyCBundle\Entity\CampoNom $campoNom
     * @return ValoresNom
     */
    public function setCampoNom(\UCI\Boson\EyCBundle\Entity\CampoNom $campoNom = null)
    {
        $this->campoNom = $campoNom;

        return $this;
    }

    /**
     * Get campoNom
     *
     * @return \UCI\Boson\EyCBundle\Entity\CampoNom
     */
    public function getCampoNom()
    {
        return $this->campoNom;
    }
}
