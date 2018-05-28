<?php

/**
 * Based off SquizLabs Sniff.
 */

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Common;
use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;

class VariableCommentSniff extends AbstractVariableSniff
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
        if ($commentEnd === false
            || ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG
            && $tokens[$commentEnd]['code'] !== T_COMMENT)
        ) {
            $phpcsFile->addError('Missing member variable doc comment', $stackPtr, 'Missing');
            return;
        }

        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            $phpcsFile->addError('You must use "/**" style comments for a member variable comment', $stackPtr, 'WrongStyle');
            return;
        }

        $commentStart = $tokens[$commentEnd]['comment_opener'];

        $foundVar = null;
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {

            if(substr($tokens[$tag]['content'], 0, 5) == '@ORM\\'){
                $phpcsFile->addError('Property annotated using the ' . $tokens[$tag]['content'] . ' Namespace, import the Annotation class instead.', $tag, 'ImportDoctrineORMFully');
            }

            if ($tokens[$tag]['content'] === '@var') {
                if ($foundVar !== null) {
                    $error = 'Only one @var tag is allowed in a member variable comment';
                    $phpcsFile->addError($error, $tag, 'DuplicateVar');
                } else {
                    $foundVar = $tag;
                }
            } else if ($tokens[$tag]['content'] === '@see') {
                // Make sure the tag isn't empty.
                $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
                if ($string === false || $tokens[$string]['line'] !== $tokens[$tag]['line']) {
                    $error = 'Content missing for @see tag in member variable comment';
                    $phpcsFile->addError($error, $tag, 'EmptySees');
                }
            }
        }

        // The @var tag is the only one we require.
        if ($foundVar === null) {
            $error = 'Missing @var tag in member variable comment';
            $phpcsFile->addError($error, $commentEnd, 'MissingVar');
            return;
        }

        // Make sure the tag isn't empty and has the correct padding.
        $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $foundVar, $commentEnd);
        if ($string === false || $tokens[$string]['line'] !== $tokens[$foundVar]['line']) {
            $error = 'Content missing for @var tag in member variable comment';
            $phpcsFile->addError($error, $foundVar, 'EmptyVar');
            return;
        }

        $varType       = $tokens[($foundVar + 2)]['content'];
        $suggestedType = Common::suggestType($varType);
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
