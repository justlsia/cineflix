<?php

namespace App\Entity;

use App\Repository\MovieRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieRepository::class)]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titleMovie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?int $idTmdb = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitleMovie(): ?string
    {
        return $this->titleMovie;
    }

    public function setTitleMovie(string $titleMovie): static
    {
        $this->titleMovie = $titleMovie;

        return $this;
    }

    public function getIdTmdb(): ?int
    {
        return $this->idTmdb;
    }

    public function setIdTmdb(?int $idTmdb): static
    {
        $this->idTmdb = $idTmdb;

        return $this;
    }

    public function setTitle(?string $titleMovie): static
    {
        $this->titleMovie = $titleMovie;

        return $this;
    }
}
