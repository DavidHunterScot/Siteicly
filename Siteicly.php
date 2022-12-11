<?php

class Siteicly
{
    public function __construct( String $inputPath, String $outputPath )
    {
        if( ! is_dir( $inputPath ) )
            die( "Input Path does not exist or is not a directory!\n" );
        
        $this->emptyDir( $outputPath );
        $this->staticify( $inputPath, $outputPath );
    }

    private function staticify( String $inputPath, String $outputPath ): void
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
            
            if( is_dir( $inputFilePath ) )
            {
                $this->staticify( $inputFilePath, $outputFilePath );
                continue;
            }

            if( is_file( $inputFilePath ) )
            {
                if( str_contains( $inputFilePath, ".inc" ) )
                    continue;

                if( substr( $inputFilePath, -4 ) == ".php" )
                {
                    $outputFilePath = str_replace( ".php", ".html", $outputFilePath );

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
            die( "Cannot empty non-existant directory: " . $dirPath . "\n" );
        
        $dirItems = scandir( $dirPath );

        if( count( $dirItems ) <= 2 )
            echo "Ignoring already empty directory: " . $dirPath;
        
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
