<?php

namespace Pantheon\Terminus\UnitTests\Commands\Org\Site;

use Pantheon\Terminus\Collections\OrganizationSiteMemberships;
use Pantheon\Terminus\Commands\Org\Site\RemoveCommand;
use Pantheon\Terminus\Models\OrganizationSiteMembership;
use Pantheon\Terminus\Models\Workflow;

/**
 * Testing class for Pantheon\Terminus\Commands\Org\Site\RemoveCommand
 */
class RemoveCommandTest extends OrgSiteCommandTest
{
    /**
     * @var OrganizationSiteMembership
     */
    protected $org_site_membership;
    /**
     * @var OrganizationSiteMemberships
     */
    protected $org_site_memberships;
    /**
     * @var Workflow
     */
    protected $workflow;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->site->id = 'site_id';

        $this->org_site_membership = $this->getMockBuilder(OrganizationSiteMembership::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->org_site_membership->site = $this->site;
        $this->org_site_memberships = $this->getMockBuilder(OrganizationSiteMemberships::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->org_site_memberships->method('get')
            ->with($this->site->id)
            ->willReturn($this->org_site_membership);
        $this->organization->method('getSiteMemberships')
            ->with()
            ->willReturn($this->org_site_memberships);

        $this->command = new RemoveCommand($this->getConfig());
        $this->command->setSites($this->sites);
        $this->command->setLogger($this->logger);
        $this->command->setSession($this->session);
    }

    /**
     * Tests the org:site:remove command
     */
    public function testRemove()
    {
        $org_name = 'Organization Name';
        $site_name = 'Site Name';

        $this->workflow = $this->getMockBuilder(Workflow::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->org_site_membership->expects($this->once())
            ->method('delete')
            ->with()
            ->willReturn($this->workflow);
        $this->workflow->expects($this->once())
            ->method('checkProgress')
            ->willReturn(true);
        $this->site->expects($this->once())
            ->method('get')
            ->with($this->equalTo('name'))
            ->willReturn($site_name);
        $this->organization->expects($this->once())
            ->method('get')
            ->with($this->equalTo('profile'))
            ->willReturn((object)['name' => $org_name,]);

        $this->logger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo('notice'),
                $this->equalTo('{site} has been removed from the {org} organization.'),
                $this->equalTo(['site' => $site_name, 'org' => $org_name,])
            );

        $out = $this->command->remove($this->organization->id, $this->site->id);
        $this->assertNull($out);
    }
}