<?php

declare(strict_types=1);

namespace Bic\Foundation\Exception\Handler;

use Bic\Foundation\Exception\HandlerInterface;

final class Win32Handler implements HandlerInterface
{
    private readonly \FFI $user32;

    public function __construct()
    {
        /** @psalm-suppress MixedAssignment */
        $this->user32 = \FFI::cdef(<<<'CLANG'
            extern int MessageBoxW(
                void* hWnd,
                const char* lpText,
                const char* lpCaption,
                unsigned int uType
            );
        CLANG, 'User32.dll');
    }

    /**
     * @param string $message
     *
     * @return string
     */
    private function toUnicode(string $message): string
    {
        if ($message === '') {
            return '';
        }

        return \iconv('utf-8', 'utf-16le', $message);
    }

    /**
     * @param \Throwable $e
     *
     * @return void
     */
    public function throw(\Throwable $e): void
    {
        $this->user32->MessageBoxW(
            null,
            $this->getText($e) . "\0\0",
            $this->getCaption($e) . "\0\0",
            0x00000010
        );
    }

    /**
     * @param \Throwable $e
     *
     * @return string
     */
    private function getCaption(\Throwable $e): string
    {
        return $this->toUnicode($e::class);
    }

    /**
     * @param \Throwable $e
     *
     * @return string
     */
    private function getText(\Throwable $e): string
    {
        $message = \sprintf(" %s\n\n %s:%d", $e->getMessage(), $e->getFile(), $e->getLine());

        return $this->toUnicode($message);
    }
}
