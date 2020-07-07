<?php

namespace DPRMC\LaravelDiskManger;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use MichaelDrennen\RemoteFile\RemoteFile;
use Exception;


class LaravelDiskManager {

    /**
     * Constants
     */
    const file_name     = 'file_name';
    const file_size     = 'file_size';
    const last_modified = 'last_modified';

    /**
     * @string
     */
    protected $disk;

    /**
     * LaravelDiskManager constructor.
     * @param $disk
     */
    public function __construct( $disk )
    {
        $this->disk = $disk;
    }

    /**
     * Returns a collection of arrays. Each array withing the collection will have a key and corresponding values for
     * file_name, file_size, and last_modified for each file within the disk location.
     * @return Collection
     */
    public function listFiles() : Collection
    {
        $files = Storage::disk($this->disk)->files();
        $transformed_files = new Collection();
        foreach ($files as $file) {
            $transformed_files->push([
                self::file_name => $file,
                self::file_size => RemoteFile::humanFileSize(Storage::disk($this->disk)->size($file)),
                self::last_modified => Carbon::parse(Storage::disk($this->disk)->lastModified($file))->format('m-d-Y')
            ]);
        }

        return $transformed_files;
    }

    /**
     * Deletes a file within disk location.  Returns true upon successful removal of the file or throws an exception if
     * the file was not able to be removed from the disk location.
     * @param String $filename
     * @return bool|Exception
     */
    public function deleteFile( String $filename )
    {
        try {
            if ( Storage::disk( $this->disk )->exists( $filename ) ) {
                Storage::disk( $this->disk )->delete( $filename );
                // If the file still exits, then there was a problem deleting the file
                throw_if( Storage::disk( $this->disk )->exists( $filename ), new Exception( 'There was a problem deleting file: ' . $filename ) );
            }
            else throw new Exception( 'File: ' . $filename . ' does not exist at disk location.' );

        } catch ( Exception $exception ) {
            return $exception;
        }

        return true;
    }

}