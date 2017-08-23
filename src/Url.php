<?php declare(strict_types=1);

namespace blitzik\Router;

use blitzik\Router\Exceptions\ParameterFilterAlreadySet;
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
    private $internalParameters = [];
    
    /** @var array */
    private $filters = [];

    
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


    public function addInternalParameter(string $name, string $value): void
    {
        $this->internalParameters[$name] = $value;
    }


    public function addFilter(string $filterName, array $applyToParameters): void
    {
        $applyToParameters = array_unique($applyToParameters);
        if (empty($applyToParameters)) {
            return;
        }

        foreach ($applyToParameters as $parameterName) {
            if (isset($this->filters[$parameterName])) {
                throw new ParameterFilterAlreadySet(sprintf('Parameter "%s" of path "%s" can be processed by only ONE Parameter Filter!', $this->urlPath, $parameterName));
            }
            $this->filters[$parameterName] = $filterName;
        }
    }


    /*
    * --------------------
    * ----- GETTERS ------
    * --------------------
    */


    public function getInternalParameter(string $name): ?string
    {
        if (isset($this->internalParameters[$name])) {
            return $this->internalParameters[$name];
        }

        return null;
    }


    public function getInternalParameters(): array
    {
        return $this->internalParameters;
    }


    public function getFilters(): array
    {
        return $this->filters;
    }


    public function getFilterByParameterName(string $parameterName): ?string
    {
        if (isset($this->filters[$parameterName])) {
            return $this->filters[$parameterName];
        }

        return null;
    }


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


    public function getPresenter(): ?string
    {
        return $this->presenter;
    }


    public function getAction(): ?string
    {
        return $this->action;
    }


    public function getDestination(): ?string
    {
        if ($this->presenter === null or $this->action === null) {
            return null;
        }

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