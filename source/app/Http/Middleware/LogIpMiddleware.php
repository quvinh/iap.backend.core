<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogIpMiddleware
{
    protected $blockedIps = [
        '176.124.192.224',
        '5.44.42.5',
        '115.134.1.245',
        '159.203.175.189',
        '216.83.41.109',
        '194.247.173.99',
        // Add here
    ];

    protected $blockedUserAgents = [
        'DataForSeoBot',
        'SemrushBot',
        'YandexBot',
        'MJ12bot',
    ];

    protected $ignoreUserAgents = [
        'Google-Apps-Script',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $ipAddress = $request->ip() ?? null;
        $agent = $request->header('User-Agent') ?? '';
        
        // Ignore UserAgents
        foreach ($this->ignoreUserAgents as $ignoreAgent) {
            if (stripos($agent, $ignoreAgent) !== false) {
                return $next($request);
            }
        }

        // Block UserAgents
        foreach ($this->blockedUserAgents as $blockedAgent) {
            if (stripos($agent, $blockedAgent) !== false) {
                // Log::channel('ips_access')->warning("🛑 Truy cập bị từ chối: {$ipAddress}", [
                //     'agent' => $agent,
                //     'full_url' => $request->fullUrl(),
                // ]);
                return response('Forbidden', 403);
            }
        }

        // Block IPs
        if (in_array($ipAddress, $this->blockedIps)) {
            // Log::channel('ips_access')->warning("🛑 Truy cập bị từ chối: {$ipAddress}", [
            //     'agent' => $agent,
            //     'full_url' => $request->fullUrl(),
            // ]);
            return response('Forbidden', 403);
        }

        Log::channel('ips_access')->info("👉 Truy cập từ IP: {$ipAddress}", [
            'agent' => $agent,
            'full_url' => $request->fullUrl(),
        ]);
        return $next($request);
    }
}
