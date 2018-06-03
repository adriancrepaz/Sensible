<?php

/**
 * Disallows the use of Doctrine's DateTime types, in favour of DateTimeImmutable types. 
 */

use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use PHP_CodeSniffer\Files\File;

class DoctrineDateTimeTypesSniff extends AbstractVariableSniff
{
    /**
     * Called to process class member vars.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    public function processMemberVar(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $ignore = [
            T_PUBLIC,
            T_PRIVATE,
            T_PROTECTED,
            T_VAR,
            T_STATIC,
            T_WHITESPACE,
        ];

        $commentEnd = $phpcsFile->findPrevious($ignore, ($stackPtr - 1), null, true);

        if(!isset($tokens[$commentEnd]['comment_opener'])){
            return;
        }

        $commentStart = $tokens[$commentEnd]['comment_opener'];

        $comment = '';

        foreach($tokens AS $pointer => $token){

            if($pointer < $commentStart){
                continue;
            }

            if($pointer > $commentEnd){
                continue;
            }

            $comment .= $token['content'];
        }

        if(strpos($comment, 'Column') !== false) {

            preg_match_all('/@(?:ORM\\\)?Column(?:.*)type="(date|datetime|datetimez)"\)/', $comment, $types);

            if(!empty($types[1][0])){

                $error = sprintf(
                    'Doctrine column type \'%s\' specified, use \'%s\' instead.',
                    $types[1][0],
                    $types[1][0] . '_immutable'
                );

                $phpcsFile->addError($error, $stackPtr, 998);
            }
        }
    }

    /**
     * Called to process a normal variable.
     *
     * Not required for this sniff.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The PHP_CodeSniffer file where this token was found.
     * @param int                         $stackPtr  The position where the double quoted
     *                                               string was found.
     *
     * @return void
     */
    protected function processVariable(File $phpcsFile, $stackPtr)
    {

    }

    /**
     * Called to process variables found in double quoted strings.
     *
     * Not required for this sniff.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The PHP_CodeSniffer file where this token was found.
     * @param int                         $stackPtr  The position where the double quoted
     *                                               string was found.
     *
     * @return void
     */
    protected function processVariableInString(File $phpcsFile, $stackPtr)
    {

    }
}
