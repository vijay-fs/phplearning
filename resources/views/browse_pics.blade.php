<!DOCTYPE html>
<html>
<head>
   <title>Browse Pics</title>
</head>
<body>
<h2>Pics</h2>

@forelse ($pics as $pic)
<p>
    File name: {{ $pic->filename }}<br>
  <img src="{{ $pic->image }}" alt="{{ $pic->filename }}">
</p>
@empty
<p>No results</p>
@endforelse
<form action="{{ route('pics.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <label for="filename">File Name:</label>
    <input type="text" name="filename" required>
    <br>
    <label for="image">Choose Image:</label>
    <input type="file" name="image" accept="image/*" required>
    <br>
    <button type="submit">Upload</button>
</form>


</body>
</html>