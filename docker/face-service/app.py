import json
import io
from fastapi import FastAPI, File, UploadFile, Form, HTTPException
from fastapi.responses import JSONResponse
import face_recognition
import numpy as np
from PIL import Image

app = FastAPI(title="Face Recognition Microservice API")

@app.get("/health")
def health_check():
    return {"status": "ok", "service": "face-recognition-api"}

@app.post("/encode")
async def encode_face(image: UploadFile = File(...)):
    try:
        contents = await image.read()
        pil_image = Image.open(io.BytesIO(contents)).convert('RGB')
        img_np = np.array(pil_image)

        locations = face_recognition.face_locations(img_np)
        if not locations:
            return JSONResponse(
                status_code=400,
                content={"success": False, "message": "Tidak ada wajah yang terdeteksi pada gambar."}
            )

        encodings = face_recognition.face_encodings(img_np, locations)
        if not encodings:
            return JSONResponse(
                status_code=400,
                content={"success": False, "message": "Gagal mengekstrak encoding wajah."}
            )

        encoding_list = encodings[0].tolist()
        return {
            "success": True,
            "message": "Encoding wajah berhasil diekstrak.",
            "encoding": encoding_list,
            "encoding_json": json.dumps(encoding_list)
        }
    except Exception as e:
        return JSONResponse(
            status_code=500,
            content={"success": False, "message": f"Error processing image: {str(e)}"}
        )

@app.post("/verify")
async def verify_face(
    image: UploadFile = File(...),
    target_encoding: str = Form(...),
    tolerance: float = Form(0.5)
):
    try:
        known_encoding = json.loads(target_encoding)
        known_encoding_np = np.array(known_encoding)

        contents = await image.read()
        pil_image = Image.open(io.BytesIO(contents)).convert('RGB')
        img_np = np.array(pil_image)

        locations = face_recognition.face_locations(img_np)
        if not locations:
            return {
                "verified": False,
                "distance": 1.0,
                "message": "Tidak ada wajah terdeteksi pada gambar absensi."
            }

        unknown_encodings = face_recognition.face_encodings(img_np, locations)
        if not unknown_encodings:
            return {
                "verified": False,
                "distance": 1.0,
                "message": "Gagal mengekstrak encoding dari gambar absensi."
            }

        distances = face_recognition.face_distance(unknown_encodings, known_encoding_np)
        min_distance = float(min(distances))
        verified = min_distance <= tolerance

        return {
            "verified": verified,
            "distance": min_distance,
            "message": "Wajah cocok" if verified else f"Wajah tidak cocok (jarak: {min_distance:.4f})"
        }
    except Exception as e:
        return JSONResponse(
            status_code=500,
            content={"verified": False, "distance": 1.0, "message": f"Error: {str(e)}"}
        )
