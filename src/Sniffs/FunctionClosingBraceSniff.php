<?php

/**
 * Based off the SquizLabs sniff.
 */

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class FunctionClosingBraceSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_FUNCTION];
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in
     *                                               the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['scope_closer']) === false) {
            // Probably an interface method.
            return;
        }

        $closeBrace  = $tokens[$stackPtr]['scope_closer'];
        $prevContent = $phpcsFile->findPrevious(T_WHITESPACE, ($closeBrace - 1), null, true);
        $found       = ($tokens[$closeBrace]['line'] - $tokens[$prevContent]['line'] - 1);

        if ($found < 0) {
            // Brace isn't on a new line, so not handled by us.
            return;
        }

        if ($found === 0) {
            // All is good.
            return;
        }

        $error = 'Function closing brace must go on the next line following the body; found %s blank lines before brace';
        $data  = [$found];
        $fix   = $phpcsFile->addFixableError($error, $closeBrace, 'SpacingBeforeClose', $data);

        if ($fix === true) {
            $phpcsFile->fixer->beginChangeset();
            for ($i = ($prevContent + 1); $i < $closeBrace; $i++) {
                if ($tokens[$i]['line'] === $tokens[$prevContent]['line']) {
                    continue;
                }

                // Don't remove any identation before the brace.
                if ($tokens[$i]['line'] === $tokens[$closeBrace]['line']) {
                    break;
                }

                $phpcsFile->fixer->replaceToken($i, '');
            }

            $phpcsFile->fixer->endChangeset();
        }
    }
}
