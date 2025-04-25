<?php
declare(strict_types=1);

namespace App\Users\User;

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
                $state['TWOFA'] === 'yes',
                $state['SUSPENDED'] === 'yes',
                $state['LOGIN_DISABLED'] === 'no',
                $state['LOGIN_ALLOW_IPS'],
            ),
            new ServerSettings(
                $state['SHELL'],
                $state['PHPCLI'],
                $state['NS'],
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
            'TWOFA' => $this->authenticationSettings->isTwoFAEnabled() ? 'yes' : 'no',
            'SUSPENDED' => $this->authenticationSettings->isSuspended() ? 'yes' : 'no',
            'LOGIN_DISABLED' => $this->authenticationSettings->isLoginEnabled() ? 'no' : 'yes',
            'LOGIN_ALLOW_IPS' => $this->authenticationSettings->getLoginIpAllowList(),
            'SHELL' => $this->serverSettings->getSshAccessShell(),
            'PHPCLI' => $this->serverSettings->getPhpCliVersion(),
            'NS' => $this->serverSettings->getDefaultNameservers(),
            'DATE' => $this->createdOn->getDate(),
            'TIME' => $this->createdOn->getTime(),
        ]
    }
}
