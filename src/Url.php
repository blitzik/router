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

    
    /*
     * --------------------
     * ----- SETTERS ------
     * --------------------
     */


    public function setUrlPath(string $path)
    {
        $this->urlPath = Strings::webalize($path, '/.');
    }


    public function setInternalId(string $internalId = null)
    {
        $this->internalId = $internalId;
    }


    public function setDestination(string $presenter, string $action = null)
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


    public function setRedirectTo(Url $actualUrlToRedirect)
    {
        $this->urlToRedirect = $actualUrlToRedirect;
    }
    

    /*
     * --------------------
     * ----- GETTERS ------
     * --------------------
     */


    public function getUrlPath():string
    {
        return $this->urlPath;
    }


    /**
     * @return string|null
     */
    public function getInternalId()
    {
        return $this->internalId;
    }


    /**
     * @return Url|null
     */
    public function getUrlToRedirect()
    {
        return $this->urlToRedirect;
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
    public function getAbsoluteDestination()
    {
        if (!isset($this->presenter, $this->action)) {
            return null;
        }

        return ':' .$this->presenter. ':' .$this->action;
    }

}