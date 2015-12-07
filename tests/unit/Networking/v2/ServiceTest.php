<?php

namespace OpenStack\Test\Networking\v2;

use OpenStack\Networking\v2\Api;
use OpenStack\Networking\v2\Models\Network;
use OpenStack\Networking\v2\Models\Port;
use OpenStack\Networking\v2\Models\Subnet;
use OpenStack\Networking\v2\Service;
use OpenStack\Test\TestCase;
use Prophecy\Argument;

class ServiceTest extends TestCase
{
    private $service;

    public function setUp()
    {
        parent::setUp();

        $this->rootFixturesDir = __DIR__;

        $this->service = new Service($this->client->reveal(), new Api());
    }

    public function test_it_creates_an_network()
    {
        $opts = [
            'name'         => 'foo',
            'shared'       => false,
            'adminStateUp' => true
        ];

        $expectedJson = ['network' => [
            'name'           => $opts['name'],
            'shared'         => $opts['shared'],
            'admin_state_up' => $opts['adminStateUp'],
        ]];

        $this->setupMock('POST', 'v2.0/networks', $expectedJson, [], 'network-post');

        $this->assertInstanceOf(Network::class, $this->service->createNetwork($opts));
    }

    public function test_it_bulk_creates_networks()
    {
        $opts = [
            [
                'name'         => 'foo',
                'shared'       => false,
                'adminStateUp' => true
            ],
            [
                'name'         => 'bar',
                'shared'       => true,
                'adminStateUp' => false
            ],
        ];

        $expectedJson = [
            'networks' => [
                [
                    'name'           => $opts[0]['name'],
                    'shared'         => $opts[0]['shared'],
                    'admin_state_up' => $opts[0]['adminStateUp']
                ],
                [
                    'name'           => $opts[1]['name'],
                    'shared'         => $opts[1]['shared'],
                    'admin_state_up' => $opts[1]['adminStateUp']
                ],
            ],
        ];

        $this->setupMock('POST', 'v2.0/networks', $expectedJson, [], 'networks-post');

        $networks = $this->service->createNetworks($opts);

        $this->assertInternalType('array', $networks);
        $this->assertCount(2, $networks);
    }

    public function test_it_gets_an_network()
    {
        $network = $this->service->getNetwork('networkId');

        $this->assertInstanceOf(Network::class, $network);
        $this->assertEquals('networkId', $network->id);
    }

    public function test_it_lists_networks()
    {
        $this->client
            ->request('GET', 'v2.0/networks', ['headers' => []])
            ->shouldBeCalled()
            ->willReturn($this->getFixture('networks-post'));

        foreach ($this->service->listNetworks() as $network) {
            $this->assertInstanceOf(Network::class, $network);
        }
    }

    public function test_it_creates_a_subnet()
    {
        $opts = [
            'name'      => 'foo',
            'networkId' => 'networkId',
            'tenantId'  => 'tenantId',
            'ipVersion' => 4,
            'cidr'      => '192.168.199.0/24',
        ];

        $expectedJson = json_encode(['subnet' => [
            'name'       => $opts['name'],
            'network_id' => $opts['networkId'],
            'tenant_id'  => $opts['tenantId'],
            'ip_version' => $opts['ipVersion'],
            'cidr'       => $opts['cidr'],
        ]], JSON_UNESCAPED_SLASHES);

        $this->setupMock('POST', 'v2.0/subnets', $expectedJson, ['Content-Type' => 'application/json'], 'subnet-post');

        $this->assertInstanceOf(Subnet::class, $this->service->createSubnet($opts));
    }

    public function test_it_bulk_creates_subnets()
    {
        $opts = [
            [
                'name'      => 'foo',
                'networkId' => 'networkId',
                'tenantId'  => 'tenantId',
                'ipVersion' => 4,
                'cidr'      => '192.168.199.0/24',
            ],
            [
                'name'      => 'bar',
                'networkId' => 'networkId',
                'tenantId'  => 'tenantId',
                'ipVersion' => 4,
                'cidr'      => '10.56.4.0/22',
            ],
        ];

        $expectedJson = json_encode([
            'subnets' => [
                [
                    'name'       => $opts[0]['name'],
                    'network_id' => $opts[0]['networkId'],
                    'tenant_id'  => $opts[0]['tenantId'],
                    'ip_version' => $opts[0]['ipVersion'],
                    'cidr'       => $opts[0]['cidr'],
                ],
                [
                    'name'       => $opts[1]['name'],
                    'network_id' => $opts[1]['networkId'],
                    'tenant_id'  => $opts[1]['tenantId'],
                    'ip_version' => $opts[1]['ipVersion'],
                    'cidr'       => $opts[1]['cidr'],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES);

        $this->setupMock('POST', 'v2.0/subnets', $expectedJson, ['Content-Type' => 'application/json'], 'subnets-post');

        $subnets = $this->service->createSubnets($opts);

        $this->assertInternalType('array', $subnets);
        $this->assertCount(2, $subnets);
    }

    public function test_it_gets_an_subnet()
    {
        $subnet = $this->service->getSubnet('subnetId');

        $this->assertInstanceOf(Subnet::class, $subnet);
        $this->assertEquals('subnetId', $subnet->id);
    }

    public function test_it_lists_subnets()
    {
        $this->client
            ->request('GET', 'v2.0/subnets', ['headers' => []])
            ->shouldBeCalled()
            ->willReturn($this->getFixture('subnets-post'));

        foreach ($this->service->listSubnets() as $subnet) {
            $this->assertInstanceOf(Subnet::class, $subnet);
        }
    }

    public function test_it_creates_a_port()
    {
        $opts = [
            'name'         => 'private-port',
            'networkId'    => 'a87cc70a-3e15-4acf-8205-9b711a3531b7',
            'adminStateUp' => true,
        ];

        $expectedJson = ['port' => [
            'name'           => $opts['name'],
            'network_id'     => $opts['networkId'],
            'admin_state_up' => $opts['adminStateUp'],
        ]];

        $this->setupMock('POST', 'v2.0/ports', $expectedJson, [], 'POST_ports');

        $this->assertInstanceOf(Port::class, $this->service->createPort($opts));
    }

    public function test_it_bulk_creates_ports()
    {
        $opts = [
            [
                'name'         => 'private-port1',
                'networkId'    => 'a87cc70a-3e15-4acf-8205-9b711a3531b7',
                'adminStateUp' => true,
            ],
            [
                'name'         => 'private-port2',
                'networkId'    => 'a87cc70a-3e15-4acf-8205-9b711a3531b7',
                'adminStateUp' => true,
            ],
        ];

        $expectedJson = [
            'ports' => [
                [
                    'name'           => $opts[0]['name'],
                    'network_id'     => $opts[0]['networkId'],
                    'admin_state_up' => $opts[0]['adminStateUp'],
                ],
                [
                    'name'           => $opts[1]['name'],
                    'network_id'     => $opts[1]['networkId'],
                    'admin_state_up' => $opts[1]['adminStateUp'],
                ],
            ],
        ];

        $this->setupMock('POST', 'v2.0/ports', $expectedJson, [], 'POST_multiple_ports');

        $ports = $this->service->createPorts($opts);

        $this->assertInternalType('array', $ports);
        $this->assertCount(2, $ports);
    }

    public function test_it_gets_an_port()
    {
        $port = $this->service->getPort('portId');

        $this->assertInstanceOf(Port::class, $port);
        $this->assertEquals('portId', $port->id);
    }

    public function test_it_lists_ports()
    {
        $this->client
            ->request('GET', 'v2.0/ports', ['headers' => []])
            ->shouldBeCalled()
            ->willReturn($this->getFixture('GET_ports'));

        foreach ($this->service->listPorts() as $port) {
            $this->assertInstanceOf(Port::class, $port);
        }
    }
}