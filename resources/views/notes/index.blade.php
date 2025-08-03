@extends('notes.layout')

@section('content')

<div class="card mt-5">
    <h2 class="card-header">Laravel CRUD Example</h2>
    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif

            <input type="text" id="search" class="form-control" placeholder="Search notes...">

        <div class="container mt-3" id="notes-container">
            <!-- النتائج بتظهر هنا -->
        </div>

        <div class="container mt-3 d-none" id="no-results">
            <div class="alert alert-warning">No notes found.</div>
        </div>
        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
            <a class="btn btn-success btn-sm" href="{{ route('notes.create') }}">
                <i class="fa fa-plus"></i> Create New Note
            </a>
        </div>

        <!-- Normal Table -->
        <table class="table table-bordered table-striped mt-4">
            <thead>
                <tr>
                    <th width="80px">No</th>
                    <th>Title</th>
                    <th>Content</th>
                    <th width="250px">Action</th>
                </tr>
            </thead>
            <tbody>
                @php $i = ($notes->currentPage() - 1) * $notes->perPage(); @endphp

                @forelse ($notes as $note)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $note->title }}</td>
                        <td>{{ $note->content }}</td>
                        <td>
                            <form action="{{ route('notes.destroy', $note->id) }}" method="POST">
                                <a class="btn btn-info btn-sm" href="{{ route('notes.show', $note->id) }}">
                                    <i class="fa-solid fa-list"></i> Show
                                </a>
                                <a class="btn btn-primary btn-sm" href="{{ route('notes.edit', $note->id) }}">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">There are no data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {!! $notes->links() !!}

    </div>
</div>

<!-- Live Search Script -->
<script>
document.getElementById('search').addEventListener('input', function () {
    const query = this.value.trim();
    const notesContainer = document.getElementById('notes-container');
    const noResultsDiv = document.getElementById('no-results');

    if (query === '') {
        notesContainer.innerHTML = '';
        noResultsDiv.classList.add('d-none');
        return;
    }
    fetch(`/search?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            notesContainer.innerHTML = '';

            if (data.length === 0) {
                noResultsDiv.classList.remove('d-none');
            } else {
                noResultsDiv.classList.add('d-none');
                data.forEach(note => {
                    notesContainer.innerHTML += `
                        <div class="card mb-2">
                            <div class="card-body">
                                <h5 class="card-title">${note.title}</h5>
                                <p class="card-text">${note.content}</p>
                            </div>
                        </div>
                    `;
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            noResultsDiv.classList.remove('d-none');
        });
});
</script>
@endsection
