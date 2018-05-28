<?php

/**
 * Ensures redundant Class, Trait & Interface comments are not used.
 */

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class RedundantClassCommentSniff implements Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_CLASS     => T_CLASS,
            T_INTERFACE => T_INTERFACE,
            T_TRAIT     => T_TRAIT
        ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $content = file_get_contents($phpcsFile->getFilename());

        preg_match('/(?:namespace)\s([a-zA-Z\\\]+)(?:;)/', $content, $namespaces);

        if(empty($namespaces[1])){
            return;
        }

        $token = $phpcsFile->getTokens()[$stackPtr];
        $next = $phpcsFile->findNext([T_WHITESPACE], $stackPtr + 1, null, true);

        if(!$next OR $phpcsFile->getTokens()[$next]['type'] !== 'T_STRING'){
            return;
        }

        $className = $phpcsFile->getTokens()[$next]['content'];
        $structureType = ucfirst(strtolower(str_replace('T_', '', $token['type'])));
        $structureTypeComment = ($structureType === 'Trait') ? 'Class' : $structureType;

        if(preg_match('/((?:\/\*\*)[\s\*]+(?:' . $structureTypeComment . ' ' . $className . ')[\s\*]+(?:@package ' . str_replace('\\', '\\\\', $namespaces[1]) . ')\s \*\/)/', $content)){
            $phpcsFile->addError('Redundant ' . $structureType . ' DocBlock.', $stackPtr, 'RedundantClassDocBlock');
        }
    }
}
