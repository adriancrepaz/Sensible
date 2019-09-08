<?php

/**
 * Based off the SquizLabs Sniff.
 *
 * Ensures variable & property length, whilst allowing certain names such as, "id".
 */

use PHP_CodeSniffer\Util\Common;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;

class VariableNameLengthSniff extends AbstractVariableSniff
{
    /**
     * @var array
     */
    private $allowed = [
        'id'
    ];

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return void
     */
    protected function processVariable(File $phpcsFile, $stackPtr)
    {
        $tokens  = $phpcsFile->getTokens();
        $varName = ltrim($tokens[$stackPtr]['content'], '$');

        $phpReservedVars = [
            '_SERVER',
            '_GET',
            '_POST',
            '_REQUEST',
            '_SESSION',
            '_ENV',
            '_COOKIE',
            '_FILES',
            'GLOBALS',
            'http_response_header',
            'HTTP_RAW_POST_DATA',
            'php_errormsg',
            'this'
        ];

        if (in_array($varName, $phpReservedVars) === true) {
            return;
        }

        if(strlen($varName) < 3 AND !in_array($varName, $this->allowed)){
            $phpcsFile->addError('$' . $varName . ' is below the minimum character length of 3. Consider using something more descriptive.', $stackPtr, 'MinimumVariableLength');
        }
    }

    /**
     * Processes class member variables.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return void
     */
    protected function processMemberVar(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $varName     = ltrim($tokens[$stackPtr]['content'], '$');
        $memberProps = $phpcsFile->getMemberProperties($stackPtr);

        if (empty($memberProps) === true) {
            // Couldn't get any info about this variable, which
            // generally means it is invalid or possibly has a parse
            // error. Any errors will be reported by the core, so
            // we can ignore it.
            return;
        }

        if(strlen($varName) < 3 AND !in_array($varName, $this->allowed)){
            $phpcsFile->addError('$' . $varName . ' is below the minimum character length of 3. Consider using something more descriptive.', $stackPtr, 'MinimumPropertyLength');
        }
    }

    /**
     * Processes the variable found within a double quoted string.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the double quoted
     *                                               string.
     *
     * @return void
     */
    protected function processVariableInString(File $phpcsFile, $stackPtr)
    {

    }
}
