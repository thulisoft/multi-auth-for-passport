<?php

namespace Thulisoft\MultiAuthForPassport;

use Carbon\Carbon;

class ProviderRepository
{
    /**
     * Find on `oauth_access_token_providers` table by `oauth_access_token_id`.
     *
     * @param  string $tokenId
     * @return \Thulisoft\MultiAuthForPassport\Provider|null
     */
    public function findForToken($tokenId)
    {
        return Provider::where('oauth_access_token_id', $tokenId)->first();
    }

    /**
     * Store new register on `oauth_access_token_providers` table.
     *
     * @param string $token
     * @param string $provider
     * @return \Thulisoft\MultiAuthForPassport\Provider
     * @throws \Exception
     */
    public function create($token, $provider)
    {
        $provider = (new Provider)->forceFill([
            'oauth_access_token_id' => $token,
            'provider' => $provider,
            'created_at' => new Carbon(),
            'updated_at' => new Carbon(),
        ]);

        $provider->save();

        return $provider;
    }
}
