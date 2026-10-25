{{-- Shared form fields for add and edit program. --}}
@csrf
{{-- old() keeps typed text after an error; $program->code fills the field when editing; '' when adding --}}
<label>Code <input type="text" name="code" value="{{ old('code', $program->code ?? '') }}" required></label><br>
<label>Name <input type="text" name="name" value="{{ old('name', $program->name ?? '') }}" required></label><br>
<button type="submit">Save</button>
<a href="{{ route('programs.index') }}">Cancel</a>
