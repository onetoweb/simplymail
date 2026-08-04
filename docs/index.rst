.. title:: Index

Index
=====

.. contents::
    :local:

===========
Basic Usage
===========

Setup

.. code-block:: php
    
    require 'vendor/autoload.php';
    
    session_start();
    
    use Onetoweb\Frama\Client;
    use Onetoweb\Frama\Token;
    
    // client parameters
    $username = 'username';
    $password = 'password';
    
    // setup client
    $client = new Client($username, $password);
    
    // set token callback to store token
    $client->setUpdateTokenCallback(function(Token $token) {
        
        $_SESSION['token'] = [
            'token' => $token->getToken(),
            'expires' => $token->getExpires(),
        ];
        
    });
    
    // load token from storage
    if (isset($_SESSION['token'])) {
        
        $token = new Token(
            $_SESSION['token']['token'],
            $_SESSION['token']['expires']
        );
        
        $client->setToken($token);
    }


========
Examples
========


Get products
````````````

.. code-block:: php
    
    $countryCode = 'NL';
    $result = $client->getProducts($countryCode);


Create shipment
```````````````

.. code-block:: php
    
    $result = $client->createShipments([
        'shipments' => [
            [
                'referenceId' => '10001',
                'carrier' => 1,
                'labelText' => 'shipment 1',
                'trackTraceEmail' => 'info@frama.nl',
                'deliveryEmail' => 'info@frama.nl',
                'costCenter' => 42,
                'addresses' => [
                    [
                        'typeId' => 1,
                        'company' => 'Frama Nederland B.V.',
                        'contact' => 'Jan Janssen',
                        'street' => 'Avelingen West',
                        'number' => '9',
                        'numberSuffix' => 'A',
                        'extraAdrress' => 'Verdieping 2',
                        'email' => 'info@frama.nl',
                        'phone' => '0183 635 777',
                        'zipCode' => '4202 MS',
                        'city' => 'Gorinchem',
                        'countryCode' => 'NL'
                    ], [
                        'typeId' => 2,
                        'company' => 'Frama Nederland B.V.',
                        'contact' => 'Jan Janssen',
                        'street' => 'Avelingen West',
                        'number' => '9',
                        'numberSuffix' => 'A',
                        'extraAdrress' => 'Verdieping 2',
                        'email' => 'info@frama.nl',
                        'phone' => '0183 635 777',
                        'zipCode' => '4202 MS',
                        'city' => 'Gorinchem',
                        'countryCode' => 'NL'
                    ]
                ],
                'properties' => [
                    'package' => 1,
                    'weight' => 3000
                ]
            ]
        ]
    ]);


Delete shipments
`````````````````

.. code-block:: php
    
    $client->deleteShipments([
        'ids' =>  [
            1000001,
            1000002
        ]
    ]);


Get labels
``````````

.. code-block:: php
    
    $result = $client->getLabels([
        'output' => 1,
        'ids' =>  [
            1000001,
            1000002
        ]
    ]);
    
    // save file
    $filename = '/path/to/filename.pdf';
    file_put_contents($filename, base64_decode($result['contentPDF']));


Get pickup points
`````````````````

.. code-block:: php
    
    $zipCode = '4202MS';
    $countryCode = 'NL';
    $result = $client->getPickupPoints($zipCode, $countryCode);


Get status
``````````

.. code-block:: php
    
    
    $barcode = '3S1100001';
    $result = $client->getStatus($barcode);
