import base64
import traceback
from fastapi import APIRouter, Request, HTTPException
from models.recognition import get_face_embedding, compare_faces

router = APIRouter()

@router.post('/attendance')
async def attendance(request: Request):
    try:
        data = await request.json()

        if 'image' not in data or 'users' not in data:
            raise HTTPException(status_code=400, detail="Data image atau user tidak ditemukan!")
        
        image_data = data['image']
        if ',' in image_data:
            image_data = image_data.split(",")[1]

        try:
            image_bytes = base64.b64decode(image_data)
        except Exception:
            raise HTTPException(status_code=400, detail="Format gambar base64 tidak valid")
        
        target_embedding = get_face_embedding(image_bytes)

        if target_embedding is None:
            return { "match": False, "message": "Wajah tidak terdeteksi", "confidence": 0 }
        
        known_users = data['users']
        user_id, score = compare_faces(target_embedding, known_users)

        if user_id:
            return {
                "match": True,
                "message": user_id,
                "confidence": score
            }

        return {
            "match": False, 
            "message": "Wajah tidak dikenali", 
            "confidence": score
        }
        
    except Exception as err:
        print(f"Error detail: {str(err)}")
        raise HTTPException(status_code=500, detail=str(err))