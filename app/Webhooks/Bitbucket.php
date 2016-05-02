<?php

namespace REBELinBLUE\Deployer\Webhooks;

/**
 * Class to handle integration with Bitbucket webhooks.
 */
class Bitbucket extends Webhook
{
    /**
     * Determines whether the request was from Bitbucket.
     *
     * @return bool
     */
    public function isRequestOrigin()
    {
        return ($this->request->headers->has('X-Event-Key'));
    }

    /**
     * Parses the request for a push webhook body.
     *
     * @return mixed Either an array of parameters for the deployment config, or false if it is invalid.
     */
    public function handlePush()
    {
        // We only care about push events
        if ($this->request->header('X-Event-Key') !== 'repo:push') {
            return false;
        }

        return false;

        $payload = $this->request->json();

        // Github sends a payload when you close a pull request with a non-existent commit.
        if ($payload->has('after') && $payload->get('after') === '0000000000000000000000000000000000000000') {
            return false;
        }

        $head   = $payload->get('head_commit');
        $branch = preg_replace('#refs/(tags|heads)/#', '', $payload->get('ref'));

        return [
            'reason'          => $head['message'],
            'branch'          => $branch,
            'source'          => 'Bitbucket',
            'build_url'       => $head['url'],
            'commit'          => $head['id'],
            'committer'       => $head['committer']['name'],
            'committer_email' => $head['committer']['email'],
        ];
    }
}
