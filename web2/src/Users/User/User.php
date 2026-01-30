<?php
declare(strict_types=1);

namespace App\Users\User;

use function explode;

class User
{
    private function __construct(
        private readonly Username $username,
        private Password $password,
        private Role $role,
        private ContactInfo $contactInfo,
        private PanelSettings $panelSettings,
        private AuthenticationSettings $authenticationSettings,
        private ServerSettings $serverSettings,
        private readonly CreatedOn $createdOn,
    ) {
    }

    public static function add(
        Username $username,
        Password $password,
        ContactInfo $contactInfo,
        PanelSettings $panelSettings,
        AuthenticationSettings $authenticationSettings,
        ServerSettings $serverSettings,
    ): self {
        return new self(
            $username,
            $password,
            new Role('user'),
            $contactInfo,
            $panelSettings,
            $authenticationSettings,
            $serverSettings,
            CreatedOn::now(),
        );
    }

    public function change(
        Password $password,
        Role $role,
        ContactInfo $contactInfo,
        PanelSettings $panelSettings,
        AuthenticationSettings $authenticationSettings,
        ServerSettings $serverSettings,
    ): void {
        $this->password = $password;
        $this->role = $role;
        $this->contactInfo = $contactInfo;
        $this->panelSettings = $panelSettings;
        $this->authenticationSettings = $authenticationSettings;
        $this->serverSettings = $serverSettings;
    }

    public function delete(): void
    {
        // Nothing here yet but we can send out events or block deletion if user doesn't allow it
    }

    /**
     * @param mixed[] $state
     */
    public static function hydrate(array $state): self
    {
        return new self(
            new Username($state['USERNAME']),
            new Password($state['PASSWORD']),
            new Role($state['ROLE']),
            new ContactInfo($state['NAME'], $state['CONTACT']),
            new PanelSettings(
                $state['LANGUAGE'],
                $state['THEME'],
                $state['PREF_UI_SORT'],
            ),
            new AuthenticationSettings(
                $state['LOGIN_DISABLED'] === 'no',
                $state['TWOFA'] === 'yes',
                $state['SUSPENDED'] === 'yes',
                explode(',', $state['LOGIN_ALLOW_IPS']),
            ),
            new ServerSettings(
                $state['SHELL'],
                $state['PHPCLI'],
                explode(',', $state['NS']),
            ),
            new CreatedOn($state['DATE'], $state['TIME']),
        );
    }

    /**
     * @return mixed[]
     */
    public function dehydate(): array
    {
        return [
            'USERNAME' => $this->username->getUsername(),
            'PASSWORD' => $this->password->getPassword(),
            'ROLE' => $this->role->getRole(),
            'NAME' => $this->contactInfo->getContactName(),
            'CONTACT' => $this->contactInfo->getEmail(),
            'LANGUAGE' => $this->panelSettings->getLanguage(),
            'THEME' => $this->panelSettings->getTheme(),
            'PREF_UI_SORT' => $this->panelSettings->getSortOrder(),
            'TWOFA' => $this->authenticationSettings->isTwoFAEnabled() ? 'yes' : '',
            'SUSPENDED' => $this->authenticationSettings->isSuspended() ? 'yes' : 'no',
            'LOGIN_DISABLED' => $this->authenticationSettings->isLoginEnabled() ? 'no' : 'yes',
            'LOGIN_ALLOW_IPS' => implode(',', $this->authenticationSettings->getLoginIpAllowList()),
            'SHELL' => $this->serverSettings->getSshAccessShell(),
            'PHPCLI' => $this->serverSettings->getPhpCliVersion(),
            'NS' => implode(',', $this->serverSettings->getDefaultNameservers()),
            'DATE' => $this->createdOn->getDate(),
            'TIME' => $this->createdOn->getTime(),
        ];
    }
}
