<?php declare(strict_types=1);

namespace blitzik\Router;

use Nette\Utils\Strings;

class Url
{
    /** @var string */
    private $urlPath;

    /** @var string */
    private $presenter;

    /** @var string */
    private $action;

    /** @var string */
    private $internalId;

    /** @var Url|null */
    private $urlToRedirect;

    /** @var bool */
    private $isOneWay = false;

    /** @var array */
    private $parameters = [];

    
    /*
     * --------------------
     * ----- SETTERS ------
     * --------------------
     */


    public function setUrlPath(string $path, bool $lower = false): void
    {
        $this->urlPath = Strings::webalize($path, '/.', $lower);
    }


    public function setInternalId(string $internalId = null): void
    {
        $this->internalId = $internalId === '' ? null : $internalId;
    }


    public function setDestination(string $presenter, string $action = null): void
    {
        if ($action === null) {
            $destination = $presenter;
        } else {
            $destination = $presenter .':'. $action;
        }

        $matches = $this->resolveDestination($destination);

        $this->presenter = $matches['modulePresenter'];
        $this->action = $matches['action'];
    }


    private function resolveDestination(string $destination): array
    {
        // ((Module:)*(Presenter)):(action)
        if (!preg_match('~^(?P<modulePresenter>(?:(?:[A-Z][a-zA-Z]*):)*(?:[A-Z][a-zA-Z]*)):(?P<action>[a-z][a-zA-Z]*)$~', $destination, $matches)) {
            throw new \InvalidArgumentException('Wrong format of given string');
        }

        return $matches;
    }


    public function setRedirectTo(Url $actualUrlToRedirect): void
    {
        $this->urlToRedirect = $actualUrlToRedirect;
    }


    public function setAsOneWay(): void
    {
        $this->isOneWay = true;
    }


    public function addParameter(string $name, string $value): void
    {
        $this->parameters[$name] = $value;
    }


    public function getParameter(string $name): ?string
    {
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        }

        return null;
    }


    public function getParameters(): array
    {
        return $this->parameters;
    }
    

    /*
     * --------------------
     * ----- GETTERS ------
     * --------------------
     */


    public function getUrlPath(): string
    {
        return $this->urlPath;
    }


    public function getInternalId(): ?string
    {
        return $this->internalId;
    }


    public function getUrlToRedirect(): ?Url
    {
        return $this->urlToRedirect;
    }


    public function isOneWay(): bool
    {
        return $this->isOneWay;
    }


    public function getCurrentUrlPath(): string
    {
        if (!isset($this->urlToRedirect)) {
            return $this->urlPath;
        }

        return $this->urlToRedirect->getUrlPath();
    }


    public function getPresenter():string
    {
        return $this->presenter;
    }


    public function getAction(): string
    {
        return $this->action;
    }


    public function getDestination(): string
    {
        return $this->presenter. ':' .$this->action;
    }


    /**
     * @return string|null
     */
    public function getAbsoluteDestination(): ?string
    {
        if (!isset($this->presenter, $this->action)) {
            return null;
        }

        return ':' .$this->presenter. ':' .$this->action;
    }

}