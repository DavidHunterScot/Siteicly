<?php

class Siteicly
{
    public function __construct( String $inputPath, String $outputPath, bool $prettyURLs = true, array $pathPrefixesToIgnore = [] )
    {
        if( ! is_dir( $inputPath ) )
            die( "Input Path does not exist or is not a directory!\n" );
        
        $this->emptyDir( $outputPath );
        $this->staticify( $inputPath, $outputPath, $prettyURLs, $pathPrefixesToIgnore );
    }

    private function staticify( String $inputPath, String $outputPath, bool $prettyURLs, array $pathPrefixesToIgnore ): void
    {
        if( ! is_dir( $inputPath ) )
            die( "Staticify Input Path does not exist or is not a directory!\n" );
        
        if( ! is_dir( $outputPath ) )
        {
            mkdir( $outputPath );
            echo "Created output path: " . $outputPath . "\n";
        }

        $inputItems = scandir( $inputPath );

        if( count( $inputItems ) <= 2 )
            echo "Input directory is empty. Nothing to output.\n";

        foreach( $inputItems as $inputItem )
        {
            if( $inputItem == "." || $inputItem == ".." )
                continue;
            
            $inputFilePath = $inputPath . DIRECTORY_SEPARATOR . $inputItem;
            $outputFilePath = $outputPath . DIRECTORY_SEPARATOR . $inputItem;
            
            foreach( $pathPrefixesToIgnore as $pathPrefixToIgnore )
                if( str_contains( $inputFilePath, $pathPrefixToIgnore ) )
                    continue(2);

            
            if( is_dir( $inputFilePath ) )
            {
                $this->staticify( $inputFilePath, $outputFilePath, $prettyURLs, $pathPrefixesToIgnore );
                continue;
            }

            if( is_file( $inputFilePath ) )
            {
                if( str_contains( $inputFilePath, ".inc" ) )
                    continue;

                if( substr( $inputFilePath, -4 ) == ".php" )
                {
                    $outputFilePath = str_replace( ".php", ".html", $outputFilePath );

                    if( $prettyURLs && $inputItem != "index.php" )
                    {
                        $outputFilePath = str_replace( ".html", "", $outputFilePath );
                        if( ! is_dir( $outputFilePath ) )
                            mkdir( $outputFilePath );
                        $outputFilePath .= DIRECTORY_SEPARATOR . "index.html";
                    }

                    $this->staticifyPHP( $inputFilePath, $outputFilePath );
                    echo "Staticified PHP File: " . $inputFilePath . " to " . $outputFilePath . "!\n";
                    continue;
                }

                copy( $inputFilePath, $outputFilePath );
                echo "Copied file " . $inputFilePath . " to " . $outputFilePath . "!\n";
            }
        }
    }

    private function staticifyPHP( String $inputFilePath, String $outputFilePath ): void
    {
        if( ! is_file( $inputFilePath ) )
            die( "Staticify PHP Input File Path does not exist or is not a file!\n" );
        
        ob_start();
    
        include( $inputFilePath );
        
        $page = ob_get_contents();
        ob_end_clean();
        
        @chmod( $outputFilePath, 0755 );
        $fw = fopen( $outputFilePath, "w" );
        fputs( $fw, $page, strlen( $page ) );
        fclose( $fw );
    }

    private function emptyDir( String $dirPath )
    {
        if( ! is_dir( $dirPath ) )
            return;
        
        $dirItems = scandir( $dirPath );
        
        foreach( $dirItems as $dirItem )
        {
            if( $dirItem == "." || $dirItem == ".." )
                continue;
            
            $dirItemPath = $dirPath . DIRECTORY_SEPARATOR . $dirItem;
            
            if( is_file( $dirItemPath ) )
            {
                unlink( $dirItemPath );
                echo "Removed file: " . $dirItemPath . "\n";
            }

            if( is_dir( $dirItemPath ) )
            {
                $this->emptyDir( $dirItemPath );
                rmdir( $dirItemPath );
                echo "Removed dir: " . $dirItemPath . "\n";
            }
        }
    }
}

header( "Content-Type: text/plain" );

new Siteicly( __DIR__ . DIRECTORY_SEPARATOR . "input", __DIR__ . DIRECTORY_SEPARATOR . "output" );
