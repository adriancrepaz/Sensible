<?php

/**
 * Disallows the use of DateTime in favour of DateTimeImmutable.
 * Unless referencing DateTime constants.
 */

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class DateTimeImmutableOnlySniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_STRING];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if($tokens[$stackPtr]['content'] !== 'DateTime'){
            return;
        }

        $previous = $phpcsFile->findPrevious([T_WHITESPACE, T_NS_SEPARATOR], $stackPtr - 1, null, true);
        if(isset($tokens[$previous]) AND $tokens[$previous]['type'] === 'T_NEW'){
            $phpcsFile->addError('Do not create instances of DateTime, use DateTimeImmutable instead.', $stackPtr, 'UseDateTimeImmutable');
            return;
        }

        if(isset($tokens[$previous]) AND $tokens[$previous]['type'] === 'T_USE'){
            return;
        }

        $next = $phpcsFile->findNext([T_WHITESPACE], $stackPtr + 1, null, true);
        if(isset($tokens[$next]) AND $tokens[$next]['type'] === 'T_DOUBLE_COLON'){

            $constant = $phpcsFile->findNext([T_STRING], $stackPtr + 1, null);

            if(isset($tokens[$constant]) AND $tokens[$constant]['content'] !== 'class'){
                return;
            }

            if(isset($tokens[$constant]) AND $tokens[$constant]['content'] === 'class'){
                $phpcsFile->addError('Do not reference the DateTime FQDN, use DateTimeImmutable instead.', $stackPtr, 999);
                return;
            }
        }

        $phpcsFile->addError('Do not use DateTime, use DateTimeImmutable instead.', $stackPtr, 'UseDateTimeImmutable');
    }
}
