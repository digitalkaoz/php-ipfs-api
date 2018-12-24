<?php

declare(strict_types=1);

/*
 * This file is part of the "php-ipfs" package.
 *
 * (c) Robert Schönthal <robert.schoenthal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IPFS\Api;

use IPFS\Annotation\Api as Endpoint;
use IPFS\Command\Command;

/**
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 * @autogenerated
 * @codeCoverageIgnore
 */
final class P2p implements Api
{
    /**
     * Close active p2p listener.
     *
     * @Endpoint(name="p2p:listener:close")
     *
     * @param string $arg P2P listener protocol Required: no
     * @param bool   $all close all listeners
     *
     * @return Command
     */
    public function listenerClose(string $arg = null, bool $all = false): Command
    {
        return new Command(__METHOD__, get_defined_vars());
    }

    /**
     * List active p2p listeners.
     *
     * @Endpoint(name="p2p:listener:ls")
     *
     * @param bool $headers print table headers (HandlerID, Protocol, Local, Remote)
     *
     * @return Command
     */
    public function listenerLs(bool $headers = false): Command
    {
        return new Command(__METHOD__, get_defined_vars());
    }

    /**
     * Forward p2p connections to a network multiaddr.
     *
     * @Endpoint(name="p2p:listener:open")
     *
     * @param string $arg  protocol identifier
     * @param string $arg1 request handling application address
     *
     * @return Command
     */
    public function listenerOpen(string $arg, string $arg1): Command
    {
        return new Command(__METHOD__, get_defined_vars());
    }

    /**
     * Close active p2p stream.
     *
     * @Endpoint(name="p2p:stream:close")
     *
     * @param string $arg stream HandlerID Required: no
     * @param bool   $all close all streams
     *
     * @return Command
     */
    public function streamClose(string $arg = null, bool $all = false): Command
    {
        return new Command(__METHOD__, get_defined_vars());
    }

    /**
     * Dial to a p2p listener.
     *
     * @Endpoint(name="p2p:stream:dial")
     *
     * @param string $arg  remote peer to connect to Required:
     * @param string $arg1 protocol identifier
     * @param string $arg2 address to listen for connection/s (default: /ip4/127
     *
     * @return Command
     */
    public function streamDial(string $arg, string $arg1, string $arg2 = null): Command
    {
        return new Command(__METHOD__, get_defined_vars());
    }

    /**
     * List active p2p streams.
     *
     * @Endpoint(name="p2p:stream:ls")
     *
     * @param bool $headers print table headers (HagndlerID, Protocol, Local, Remote)
     *
     * @return Command
     */
    public function streamLs(bool $headers = false): Command
    {
        return new Command(__METHOD__, get_defined_vars());
    }
}