<?php

/*
 * @copyright   2020 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\UserBundle\Tests\Security\SAML\Store;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\UserBundle\Security\SAML\Store\EntityDescriptorStore;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EntityDescriptorStoreTest extends TestCase
{
    /**
     * @var CoreParametersHelper|MockObject
     */
    private $coreParametersHelper;

    protected function setUp()
    {
        $this->coreParametersHelper = $this->createMock(CoreParametersHelper::class);
    }

    public function testNullIsReturnedIfEntityIdDoesNotMatch()
    {
        $store = new EntityDescriptorStore($this->coreParametersHelper);

        $this->coreParametersHelper->method('get')
            ->with('saml_idp_metadata')
            ->willReturn(
                'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48bWQ6RW50aXR5RGVzY3JpcHRvciB4bWxuczptZD0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOm1ldGFkYXRhIiBlbnRpdHlJRD0iaHR0cHM6Ly9tYXV0aWMtZGV2LWVkLm15LnNhbGVzZm9yY2UuY29tIiB2YWxpZFVudGlsPSIyMDI5LTEyLTI4VDE0OjUyOjA2LjIyMFoiIHhtbG5zOmRzPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjIj4KICAgPG1kOklEUFNTT0Rlc2NyaXB0b3IgcHJvdG9jb2xTdXBwb3J0RW51bWVyYXRpb249InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDpwcm90b2NvbCI+CiAgICAgIDxtZDpLZXlEZXNjcmlwdG9yIHVzZT0ic2lnbmluZyI+CiAgICAgICAgIDxkczpLZXlJbmZvPgogICAgICAgICAgICA8ZHM6WDUwOURhdGE+CiAgICAgICAgICAgICAgIDxkczpYNTA5Q2VydGlmaWNhdGU+TUlJRVpEQ0NBMHlnQXdJQkFnSU9BVzlNNS9Nb0FBQUFBRUpEc2Vjd0RRWUpLb1pJaHZjTkFRRUxCUUF3ZWpFU01CQUdBMVVFQXd3SlUwRk5URjkwWlhOME1SZ3dGZ1lEVlFRTERBOHdNRVJxTURBd01EQXdNRXd3U1RreEZ6QVZCZ05WQkFvTURsTmhiR1Z6Wm05eVkyVXVZMjl0TVJZd0ZBWURWUVFIREExVFlXNGdSbkpoYm1OcGMyTnZNUXN3Q1FZRFZRUUlEQUpEUVRFTU1Bb0dBMVVFQmhNRFZWTkJNQjRYRFRFNU1USXlPREUwTWpjME4xb1hEVEl3TVRJeU9ERXlNREF3TUZvd2VqRVNNQkFHQTFVRUF3d0pVMEZOVEY5MFpYTjBNUmd3RmdZRFZRUUxEQTh3TUVScU1EQXdNREF3TUV3d1NUa3hGekFWQmdOVkJBb01EbE5oYkdWelptOXlZMlV1WTI5dE1SWXdGQVlEVlFRSERBMVRZVzRnUm5KaGJtTnBjMk52TVFzd0NRWURWUVFJREFKRFFURU1NQW9HQTFVRUJoTURWVk5CTUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEFNSUlCQ2dLQ0FRRUFoVVBxVEoyQ3YreVhPYzcwaW13d05IWE44OTBzQzliU1FsU05MbnJ6cHN5MFB4R0paQmRuL3hIWVlVS2FUZWxvMytHOXRGL1BIQkdHQlMrMGZPN0Rjd254KzVKRnhUQW1MR0ptdnBTN2UrdWc0T2F1SDNidWQ0ck9kbnVzNTczUjd5SjNPZi9IT25DTEpNN3R4TGxaMUorZmUxT2FkOVhHK1dWZGIvL1U0UzBqU09Lb1c5QVlxQjlPd0pLak1aNm9GWXFnQnltZzBiRS9YRFZyTHZZcktNMEkwaEpUQzQ2R1pVc1ZJZUZGM1lDVWtxcDhTZkYzWlFUZzF5SHltbjZiOHJvQjZYVy9yd3dUWVR5MFkwOFlYR0ltWEVseTVoTXFRQ25zc3BjNnJwa3VuUHlqSUY5TlV2NHBCeEU3SXhQcFFld0NrbjBGdVNIRVJQQUM5MVA5eHdJREFRQUJvNEhuTUlIa01CMEdBMVVkRGdRV0JCUlVFVnlKUSs2czdGUzhsM210R3V1ZmpMQXpnVEFQQmdOVkhSTUJBZjhFQlRBREFRSC9NSUd4QmdOVkhTTUVnYWt3Z2FhQUZGUVJYSWxEN3F6c1ZMeVhlYTBhNjUrTXNET0JvWDZrZkRCNk1SSXdFQVlEVlFRRERBbFRRVTFNWDNSbGMzUXhHREFXQmdOVkJBc01EekF3Ukdvd01EQXdNREF3VERCSk9URVhNQlVHQTFVRUNnd09VMkZzWlhObWIzSmpaUzVqYjIweEZqQVVCZ05WQkFjTURWTmhiaUJHY21GdVkybHpZMjh4Q3pBSkJnTlZCQWdNQWtOQk1Rd3dDZ1lEVlFRR0V3TlZVMEdDRGdGdlRPZnpLQUFBQUFCQ1E3SG5NQTBHQ1NxR1NJYjNEUUVCQ3dVQUE0SUJBUUE4U3NDS3lMVXE5L25RYXpxK1B0N1RRWWpMaVBWMldOeVcxeEFGQWQxekFEcW5vR1ovZFRZNkQrdTVrZExuK3paUEptaXFuVGdad01Rc3AxdXJ3SmlaK3JncXg0R3hkRlhPakZRTTZnV2RjN0xuSTJxcTI1M2F4SHRaZFNuVTE5NDFWaEc5RXVSdDNIa2tLR3VOVGUwK05GTGJKYXR6Tk04bW80dGZ4Vkxub3NxWUFSTFEvaHVKUURYUUVhcE90ZUhxYkVJbE1OTjJGUi9hYk9lNTRlaWpSRmFncXJqWEtwMlVJTFh2NEFIcE5YVjI2ek43WVpOKzhJc1pPam9RYUtLYlB4MStwRWk1NzZvQlFSSUZ1N01sRkNsc3h0QW9DNmpPb1dCV01QbXR5UGxTNEdKWlRrY056UHJNbGxRem9uZWRGWDlvTk9ZRExiRnRlak1jOWlmWjwvZHM6WDUwOUNlcnRpZmljYXRlPgogICAgICAgICAgICA8L2RzOlg1MDlEYXRhPgogICAgICAgICA8L2RzOktleUluZm8+CiAgICAgIDwvbWQ6S2V5RGVzY3JpcHRvcj4KICAgICAgPG1kOlNpbmdsZUxvZ291dFNlcnZpY2UgQmluZGluZz0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOmJpbmRpbmdzOkhUVFAtUE9TVCIgTG9jYXRpb249Imh0dHBzOi8vbWF1dGljLWRldi1lZC5teS5zYWxlc2ZvcmNlLmNvbS9zZXJ2aWNlcy9hdXRoL2lkcC9zYW1sMi9sb2dvdXQiLz4KICAgICAgPG1kOlNpbmdsZUxvZ291dFNlcnZpY2UgQmluZGluZz0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOmJpbmRpbmdzOkhUVFAtUmVkaXJlY3QiIExvY2F0aW9uPSJodHRwczovL21hdXRpYy1kZXYtZWQubXkuc2FsZXNmb3JjZS5jb20vc2VydmljZXMvYXV0aC9pZHAvc2FtbDIvbG9nb3V0Ii8+CiAgICAgIDxtZDpOYW1lSURGb3JtYXQ+dXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6MS4xOm5hbWVpZC1mb3JtYXQ6dW5zcGVjaWZpZWQ8L21kOk5hbWVJREZvcm1hdD4KICAgICAgPG1kOlNpbmdsZVNpZ25PblNlcnZpY2UgQmluZGluZz0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOmJpbmRpbmdzOkhUVFAtUE9TVCIgTG9jYXRpb249Imh0dHBzOi8vbWF1dGljLWRldi1lZC5teS5zYWxlc2ZvcmNlLmNvbS9pZHAvZW5kcG9pbnQvSHR0cFBvc3QiLz4KICAgICAgPG1kOlNpbmdsZVNpZ25PblNlcnZpY2UgQmluZGluZz0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOmJpbmRpbmdzOkhUVFAtUmVkaXJlY3QiIExvY2F0aW9uPSJodHRwczovL21hdXRpYy1kZXYtZWQubXkuc2FsZXNmb3JjZS5jb20vaWRwL2VuZHBvaW50L0h0dHBSZWRpcmVjdCIvPgogICA8L21kOklEUFNTT0Rlc2NyaXB0b3I+CjwvbWQ6RW50aXR5RGVzY3JpcHRvcj4='
            );

        $descriptor = $store->get('foobar');

        $this->assertNull($descriptor);
    }

    public function testHasReturnsFalseIfSamlIsDisabled()
    {
        $store = new EntityDescriptorStore($this->coreParametersHelper);

        $this->coreParametersHelper->method('get')
            ->with('saml_idp_metadata')
            ->willReturn('');

        $this->assertFalse($store->has('foobar'));
    }

    public function testHasReturnsFalseIfEntityIdDoesNotMatch()
    {
        $store = new EntityDescriptorStore($this->coreParametersHelper);

        $this->coreParametersHelper->method('get')
            ->with('saml_idp_metadata')
            ->willReturn(
                'PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48bWQ6RW50aXR5RGVzY3JpcHRvciB4bWxuczptZD0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOm1ldGFkYXRhIiBlbnRpdHlJRD0iaHR0cHM6Ly9tYXV0aWMtZGV2LWVkLm15LnNhbGVzZm9yY2UuY29tIiB2YWxpZFVudGlsPSIyMDI5LTEyLTI4VDE0OjUyOjA2LjIyMFoiIHhtbG5zOmRzPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwLzA5L3htbGRzaWcjIj4KICAgPG1kOklEUFNTT0Rlc2NyaXB0b3IgcHJvdG9jb2xTdXBwb3J0RW51bWVyYXRpb249InVybjpvYXNpczpuYW1lczp0YzpTQU1MOjIuMDpwcm90b2NvbCI+CiAgICAgIDxtZDpLZXlEZXNjcmlwdG9yIHVzZT0ic2lnbmluZyI+CiAgICAgICAgIDxkczpLZXlJbmZvPgogICAgICAgICAgICA8ZHM6WDUwOURhdGE+CiAgICAgICAgICAgICAgIDxkczpYNTA5Q2VydGlmaWNhdGU+TUlJRVpEQ0NBMHlnQXdJQkFnSU9BVzlNNS9Nb0FBQUFBRUpEc2Vjd0RRWUpLb1pJaHZjTkFRRUxCUUF3ZWpFU01CQUdBMVVFQXd3SlUwRk5URjkwWlhOME1SZ3dGZ1lEVlFRTERBOHdNRVJxTURBd01EQXdNRXd3U1RreEZ6QVZCZ05WQkFvTURsTmhiR1Z6Wm05eVkyVXVZMjl0TVJZd0ZBWURWUVFIREExVFlXNGdSbkpoYm1OcGMyTnZNUXN3Q1FZRFZRUUlEQUpEUVRFTU1Bb0dBMVVFQmhNRFZWTkJNQjRYRFRFNU1USXlPREUwTWpjME4xb1hEVEl3TVRJeU9ERXlNREF3TUZvd2VqRVNNQkFHQTFVRUF3d0pVMEZOVEY5MFpYTjBNUmd3RmdZRFZRUUxEQTh3TUVScU1EQXdNREF3TUV3d1NUa3hGekFWQmdOVkJBb01EbE5oYkdWelptOXlZMlV1WTI5dE1SWXdGQVlEVlFRSERBMVRZVzRnUm5KaGJtTnBjMk52TVFzd0NRWURWUVFJREFKRFFURU1NQW9HQTFVRUJoTURWVk5CTUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEFNSUlCQ2dLQ0FRRUFoVVBxVEoyQ3YreVhPYzcwaW13d05IWE44OTBzQzliU1FsU05MbnJ6cHN5MFB4R0paQmRuL3hIWVlVS2FUZWxvMytHOXRGL1BIQkdHQlMrMGZPN0Rjd254KzVKRnhUQW1MR0ptdnBTN2UrdWc0T2F1SDNidWQ0ck9kbnVzNTczUjd5SjNPZi9IT25DTEpNN3R4TGxaMUorZmUxT2FkOVhHK1dWZGIvL1U0UzBqU09Lb1c5QVlxQjlPd0pLak1aNm9GWXFnQnltZzBiRS9YRFZyTHZZcktNMEkwaEpUQzQ2R1pVc1ZJZUZGM1lDVWtxcDhTZkYzWlFUZzF5SHltbjZiOHJvQjZYVy9yd3dUWVR5MFkwOFlYR0ltWEVseTVoTXFRQ25zc3BjNnJwa3VuUHlqSUY5TlV2NHBCeEU3SXhQcFFld0NrbjBGdVNIRVJQQUM5MVA5eHdJREFRQUJvNEhuTUlIa01CMEdBMVVkRGdRV0JCUlVFVnlKUSs2czdGUzhsM210R3V1ZmpMQXpnVEFQQmdOVkhSTUJBZjhFQlRBREFRSC9NSUd4QmdOVkhTTUVnYWt3Z2FhQUZGUVJYSWxEN3F6c1ZMeVhlYTBhNjUrTXNET0JvWDZrZkRCNk1SSXdFQVlEVlFRRERBbFRRVTFNWDNSbGMzUXhHREFXQmdOVkJBc01EekF3Ukdvd01EQXdNREF3VERCSk9URVhNQlVHQTFVRUNnd09VMkZzWlhObWIzSmpaUzVqYjIweEZqQVVCZ05WQkFjTURWTmhiaUJHY21GdVkybHpZMjh4Q3pBSkJnTlZCQWdNQWtOQk1Rd3dDZ1lEVlFRR0V3TlZVMEdDRGdGdlRPZnpLQUFBQUFCQ1E3SG5NQTBHQ1NxR1NJYjNEUUVCQ3dVQUE0SUJBUUE4U3NDS3lMVXE5L25RYXpxK1B0N1RRWWpMaVBWMldOeVcxeEFGQWQxekFEcW5vR1ovZFRZNkQrdTVrZExuK3paUEptaXFuVGdad01Rc3AxdXJ3SmlaK3JncXg0R3hkRlhPakZRTTZnV2RjN0xuSTJxcTI1M2F4SHRaZFNuVTE5NDFWaEc5RXVSdDNIa2tLR3VOVGUwK05GTGJKYXR6Tk04bW80dGZ4Vkxub3NxWUFSTFEvaHVKUURYUUVhcE90ZUhxYkVJbE1OTjJGUi9hYk9lNTRlaWpSRmFncXJqWEtwMlVJTFh2NEFIcE5YVjI2ek43WVpOKzhJc1pPam9RYUtLYlB4MStwRWk1NzZvQlFSSUZ1N01sRkNsc3h0QW9DNmpPb1dCV01QbXR5UGxTNEdKWlRrY056UHJNbGxRem9uZWRGWDlvTk9ZRExiRnRlak1jOWlmWjwvZHM6WDUwOUNlcnRpZmljYXRlPgogICAgICAgICAgICA8L2RzOlg1MDlEYXRhPgogICAgICAgICA8L2RzOktleUluZm8+CiAgICAgIDwvbWQ6S2V5RGVzY3JpcHRvcj4KICAgICAgPG1kOlNpbmdsZUxvZ291dFNlcnZpY2UgQmluZGluZz0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOmJpbmRpbmdzOkhUVFAtUE9TVCIgTG9jYXRpb249Imh0dHBzOi8vbWF1dGljLWRldi1lZC5teS5zYWxlc2ZvcmNlLmNvbS9zZXJ2aWNlcy9hdXRoL2lkcC9zYW1sMi9sb2dvdXQiLz4KICAgICAgPG1kOlNpbmdsZUxvZ291dFNlcnZpY2UgQmluZGluZz0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOmJpbmRpbmdzOkhUVFAtUmVkaXJlY3QiIExvY2F0aW9uPSJodHRwczovL21hdXRpYy1kZXYtZWQubXkuc2FsZXNmb3JjZS5jb20vc2VydmljZXMvYXV0aC9pZHAvc2FtbDIvbG9nb3V0Ii8+CiAgICAgIDxtZDpOYW1lSURGb3JtYXQ+dXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6MS4xOm5hbWVpZC1mb3JtYXQ6dW5zcGVjaWZpZWQ8L21kOk5hbWVJREZvcm1hdD4KICAgICAgPG1kOlNpbmdsZVNpZ25PblNlcnZpY2UgQmluZGluZz0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOmJpbmRpbmdzOkhUVFAtUE9TVCIgTG9jYXRpb249Imh0dHBzOi8vbWF1dGljLWRldi1lZC5teS5zYWxlc2ZvcmNlLmNvbS9pZHAvZW5kcG9pbnQvSHR0cFBvc3QiLz4KICAgICAgPG1kOlNpbmdsZVNpZ25PblNlcnZpY2UgQmluZGluZz0idXJuOm9hc2lzOm5hbWVzOnRjOlNBTUw6Mi4wOmJpbmRpbmdzOkhUVFAtUmVkaXJlY3QiIExvY2F0aW9uPSJodHRwczovL21hdXRpYy1kZXYtZWQubXkuc2FsZXNmb3JjZS5jb20vaWRwL2VuZHBvaW50L0h0dHBSZWRpcmVjdCIvPgogICA8L21kOklEUFNTT0Rlc2NyaXB0b3I+CjwvbWQ6RW50aXR5RGVzY3JpcHRvcj4='
            );

        $this->assertFalse($store->has('foobar'));
    }
}
