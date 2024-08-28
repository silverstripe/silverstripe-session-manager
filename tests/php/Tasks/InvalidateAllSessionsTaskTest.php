<?php

namespace SilverStripe\SessionManager\Tests\Services;

use SilverStripe\Control\Middleware\ConfirmationMiddleware\Url;
use SilverStripe\Control\Tests\HttpRequestMockBuilder;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\AuthenticationHandler;
use SilverStripe\Security\Member;
use SilverStripe\Security\RememberLoginHash;
use SilverStripe\Security\Security;
use SilverStripe\SessionManager\Extensions\RememberLoginHashExtension;
use SilverStripe\SessionManager\Middleware\LoginSessionMiddleware;
use SilverStripe\SessionManager\Models\LoginSession;
use SilverStripe\SessionManager\Tasks\InvalidateAllSessionsTask;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class InvalidateAllSessionsTaskTest extends SapphireTest
{
    use HttpRequestMockBuilder;

    protected static $fixture_file = 'InvalidateAllSessionsTaskTest.yml';

    protected static $required_extensions = [
        RememberLoginHash::class => [
            RememberLoginHashExtension::class,
        ],
    ];

    public function testRemoveAllSessions()
    {
        // Ensure existing fixtured sessions are valid
        DBDatetime::set_mock_now('2003-02-15 10:00:00');

        // Log in as member 1 and make them active
        $request = $this->buildRequestMock('dev/tasks/' . InvalidateAllSessionsTask::config()->get('segment'));
        $request->method('getIP')->willReturn('192.168.0.1');
        $member = $this->objFromFixture(Member::class, 'member1');
        $handler = Injector::inst()->get(AuthenticationHandler::class);
        $handler->login($member, true, $request);

        // Ensure fixtured sessions are created and at least one login hash is generated
        $this->assertNotSame(0, LoginSession::get()->count(), 'There is at least one LoginSession');
        $this->assertNotSame(0, RememberLoginHash::get()->count(), 'There is at least one RememberLoginHash');

        // Completely invalidate all current sessions and login hashes
        $task = new InvalidateAllSessionsTask();
        $buffer = new BufferedOutput();
        $output = new PolyOutput(PolyOutput::FORMAT_ANSI, wrappedOutput: $buffer);
        $input = new ArrayInput([]);
        $input->setInteractive(false);
        $task->run($input, $output);

        // Assert all sessions are removed from the database
        $this->assertSame(0, LoginSession::get()->count(), 'There are no LoginSessions');
        $this->assertSame(0, RememberLoginHash::get()->count(), 'There are no RememberLoginHashes');

        // Navigate to re-validate current session
        $middleware = new LoginSessionMiddleware(new Url('no-match'));
        $next = false;
        $middleware->process(
            $request,
            function () {
                // no-op
            }
        );
        // Assert user who initiated the request's session was also invalidated
        $this->assertNull(
            Security::getCurrentUser(),
            'User was logged out because their session was invalidated'
        );
    }
}
