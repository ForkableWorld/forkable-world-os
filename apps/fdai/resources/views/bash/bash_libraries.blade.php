# SOURCED_FILES_GO_HERE TO ALLOW IDE AUTOCOMPLETION AND EASY JUMPING
@foreach($file->getLibraryScripts() as $lib)
	# shellcheck source=./{{$file->getDotsPathToRoot()}}/{{$lib->getRelativePath()}}
	source "$QM_API"/{{$lib->getRelativePath()}}
@endforeach
# SOURCED_FILES_END_HERE
