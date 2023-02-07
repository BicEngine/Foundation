<?php

declare(strict_types=1);

namespace Bic\Foundation\Kernel;

interface RunnableInterface
{
    /**
     * Run the event loop until there are no more tasks to perform.
     *
     * ```php
     *  $app->run();
     * ```
     *
     * This method will keep the loop running until there are no more tasks
     * to perform. In other words: This method will block until the last
     * timer, stream and/or signal has been removed.
     *
     * Likewise, it is imperative to ensure the application actually invokes
     * this method once. Adding listeners to the loop and missing to actually
     * run it will result in the application exiting without actually waiting
     * for any of the attached listeners.
     *
     * @return void
     */
    public function run(): void;

    /**
     * Schedule an exit from the application.
     *
     * ```php
     *  $app->exit();
     * ```
     *
     * @return void
     */
    public function exit(): void;
}
