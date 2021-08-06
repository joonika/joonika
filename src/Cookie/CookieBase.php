<?php


namespace Joonika\Cookie;
use Carbon\Carbon;


class CookieBase
{


    /**
     * The default path (if specified).
     *
     * @var string
     */
    protected $path = '/';

    /**
     * The default domain (if specified).
     *
     * @var string
     */
    protected $domain;

    /**
     * The default secure setting (defaults to false).
     *
     * @var bool
     */
    protected $secure = false;

    /**
     * The default SameSite option (if specified).
     *
     * @var string
     */
    protected $sameSite;

    /**
     * All of the cookies queued for sending.
     *
     * @var \Symfony\Component\HttpFoundation\Cookie[]
     */
    protected $queued = [];

    /**
     * Create a new cookie instance.
     *
     * @param string $name
     * @param string $value
     * @param int $minutes
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @param bool $httpOnly
     * @param bool $raw
     * @param string|null $sameSite
     * @return true;
     */
    public function make($name, $value, $minutes = 0, $path = null, $domain = null, $secure = null, $httpOnly = true, $raw = false, $sameSite = null, $crypt = false)
    {
        [$path, $domain, $secure, $sameSite] = $this->getPathAndDomain($path, $domain, $secure, $sameSite);
        $time = ($minutes == 0) ? 0 : $this->availableAt($minutes * 60);
        (new CookieEx($name, $value, $time, $path, $domain, $secure, $httpOnly, $raw, $sameSite, $crypt))->make();
        return true;
    }

    /**
     * Create a new cookie instance.
     *
     * @param string $name
     * @param string $value
     * @param int $minutes
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @param bool $httpOnly
     * @param bool $raw
     * @param string|null $sameSite
     * @return true;
     */
    public function get($name, $crypt = false)
    {
        return (new CookieEx($name, null, 1, '/', null, null, true, false, null, $crypt))->get();
    }

    /**
     * If the given value is an interval, convert it to a DateTime instance.
     *
     * @param \DateTimeInterface|\DateInterval|int $delay
     * @return \DateTimeInterface|int
     */
    protected function parseDateInterval($delay)
    {
        if ($delay instanceof \DateInterval) {
            $delay = Carbon::now()->add($delay);
        }

        return $delay;
    }

    /**
     * Get the "available at" UNIX timestamp.
     *
     * @param \DateTimeInterface|\DateInterval|int $delay
     * @return int
     */
    protected function availableAt($delay = 0)
    {
        $delay = $this->parseDateInterval($delay);

        return $delay instanceof \DateTimeInterface
            ? $delay->getTimestamp()
            : Carbon::now()->addRealSeconds($delay)->getTimestamp();
    }

    /**
     * Get the path and domain, or the default values.
     *
     * @param string $path
     * @param string $domain
     * @param bool|null $secure
     * @param string|null $sameSite
     * @return array
     */
    protected function getPathAndDomain($path, $domain, $secure = null, $sameSite = null)
    {
        return [$path ?: $this->path, $domain ?: $this->domain, is_bool($secure) ? $secure : $this->secure, $sameSite ?: $this->sameSite];
    }

    /**
     * Set the default path and domain for the jar.
     *
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param string|null $sameSite
     * @return $this
     */
    protected function setDefaultPathAndDomain($path, $domain, $secure = false, $sameSite = null)
    {
        [$this->path, $this->domain, $this->secure, $this->sameSite] = [$path, $domain, $secure, $sameSite];

        return $this;
    }
}
