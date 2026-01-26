<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SessionController extends Controller
{
    protected $path = 'sessions/';

    public function create(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'expire_at' => 'required|integer',
        ]);

        // 1️⃣ Hapus session lama user ini
        foreach (Storage::files($this->path) as $file) {
            if (str_starts_with(basename($file), $request->user_id.'_')) {
                Storage::delete($file);
            }
        }

        // 2️⃣ Generate metadata
        $createdAt = now();
        $createdAtForFilename = $createdAt->format('Ymd_His');

        // 3️⃣ session_id === filename (tanpa .json)
        $sessionId = sprintf(
            '%s_%s_%s',
            $request->user_id,
            $createdAtForFilename,
            uniqid('sess_', true)
        );

        $data = [
            'user_id' => $request->user_id,
            'created_at' => $createdAt->timestamp,
            'expire_at' => $request->expire_at, // dari JWT exp
        ];

        // 4️⃣ Simpan file
        Storage::put(
            $this->path.$sessionId.'.json',
            json_encode($data)
        );

        return response()->json([
            'session_id' => $sessionId, // 🔥 SAMA DENGAN FILENAME
            'data' => $data,
        ]);
    }

    public function verify($sessionId)
    {
        if (! Storage::exists($this->path.$sessionId.'.json')) {
            return response()->json(['message' => 'Session not found'], 401);
        }

        $data = json_decode(Storage::get($this->path.$sessionId.'.json'), true);

        if ($data['expire_at'] < now()->timestamp) {
            Storage::delete($this->path.$sessionId.'.json'); // hapus session expired

            return response()->json(['message' => 'Session expired'], 401);
        }

        return response()->json(['session' => $data]);
    }

    public function destroy($sessionId)
    {
        if (Storage::exists($this->path.$sessionId.'.json')) {
            Storage::delete($this->path.$sessionId.'.json');

            return response()->json(['message' => 'Session destroyed']);
        }

        return response()->json(['message' => 'Session not found'], 404);
    }
}
