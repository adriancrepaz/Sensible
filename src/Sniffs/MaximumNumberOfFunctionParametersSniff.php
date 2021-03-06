<?php

/**
 * Based off Coders' Sniff.
 */

use PHP_CodeSniffer\Sniffs\Sniff as PHP_CodeSniffer_Sniff;
use PHP_CodeSniffer\Files\File as PHP_CodeSniffer_File;

class MaximumNumberOfFunctionParametersSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * The maximum number of parameters a function can have.
     * @var integer
     */
    public $maxParameters = 7;

    /**
     * Returns the token types that this sniff is interested in.
     * @return array
     */
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * Processes the tokens that this sniff is interested in.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where the token was found.
     * @param integer              $stackPtr  The position in the stack where
     *                                    the token was found.
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens           = $phpcsFile->getTokens();
        $token            = $tokens[$stackPtr];
        $openParenIndex   = $token['parenthesis_opener'];
        $closedParenIndex = $token['parenthesis_closer'];

        $numberOfParameters = 0;
        for ($index=$openParenIndex+1; $index <= $closedParenIndex; $index++) {
            $tokens[$index]['type'] == 'T_VARIABLE' ? $numberOfParameters++ : null;
        }

        if ($numberOfParameters > $this->maxParameters) {
            $phpcsFile->addError("Function has more than {$this->maxParameters} parameters. Please reduce.", $stackPtr, __CLASS__ . 'MoreThan');
        }

        if ($numberOfParameters == $this->maxParameters) {
            $phpcsFile->addWarning("Function has {$this->maxParameters} parameters.", $stackPtr, __CLASS__ . 'hasMax');
        }
    }
}
