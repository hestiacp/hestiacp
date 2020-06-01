<?php

/*
 * This file is part of the FileGator package.
 *
 * (c) Milos Stojanovic <alcalbg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 */

namespace Filegator\Services\Session\Adapters;

use Filegator\Kernel\Request;
use Filegator\Services\Service;
use Filegator\Services\Session\Session;
use Filegator\Services\Session\SessionStorageInterface;

class SessionStorage implements Service, SessionStorageInterface
{
    protected $request;

    protected $config;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function init(array $config = [])
    {
        // we don't have a previous session attached
        if (! $this->getSession()) {
            $handler = $config['handler'];
            $session = new Session($handler());
            //$session->setName('filegator');
            $this->setSession($session);
        }
    }

    public function save()
    {
        $this->getSession()->save();
    }

    public function set(string $key, $data)
    {
        return $this->getSession()->set($key, $data);
    }

    public function get(string $key, $default = null)
    {
        return $this->getSession() ? $this->getSession()->get($key, $default) : $default;
    }

    public function invalidate()
    {
        if (! $this->getSession()->isStarted()) {
            $this->getSession()->start();
        }

        $this->getSession()->invalidate();
    }

    private function setSession(Session $session)
    {
        return $this->request->setSession($session);
    }

    private function getSession(): ?Session
    {
        return $this->request->getSession();
    }
}
