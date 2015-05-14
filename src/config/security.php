<?php

// This array is used as the configuration array of the symfony security
// component.
// Silex docs: http://silex.sensiolabs.org/doc/providers/security.html
return [
    'security.firewalls' => [
        'admin' => [
            'pattern' => '^/admin',
            // 'http' => true, // If you want to use basic http auth instead of form
            'form' => [
                'login_path' => '/login',
                'check_path' => '/admin/login_check'
            ],
            'logout' => ['logout_path' => '/admin/logout'],
            'users' => [ // A UserProvider implementation could be used here
                // Password generation: `generate:adminpass <raw-password>` command.
                // The raw password is: "admin".
                'admin' => ['ROLE_ADMIN', 'nhDr7OyKlXQju+Ge/WKGrPQ9lPBSUFfpK+B1xqx/+8zLZqRNX0+5G1zBQklXUFy86lCpkAofsExlXiorUcKSNQ=='],
            ],
            'context' => 'admin',
        ],
        // The API can have secured areas. For this reason, it's context is shared
        // with the "admin" firewall.
        'api' => [
            'pattern' => '^/api',
            'context' => 'admin',
            'anonymous' => true,
        ],
    ],
    // 'security.access_rules' => [
    //     ['^/admin', 'ROLE_ADMIN'] // This is needed if the whole site allows "anonymous" login
    // ],
];

// NOTE
// Another use-case could be, if the site has user registration, and it also has
// an admin area.
// In that case, the whole site should be "secured" by the firewall
// (pattern: "/") but anonymous login then must be allowed.
// Then the "admin/" routes must be protected, so that only users with
// "ROLE_ADMIN" role can access it.
// (`->secure('ROLE_ADMIN')` method can be used for that, or the
// 'security.access_rules' part of the security configuration.)
