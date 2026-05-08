from fastapi import APIRouter, UploadFile, File
from models.recognition import get_face_embedding

router = APIRouter()

@router.post("/registration")
async def extract_face_embedded(file: UploadFile = File(...)):
    content = await file.read()
    embedding = get_face_embedding(content)

    if embedding is None:
        return {
            'success': False,
            'message': "Wajah tidak terdeteksi",
        }
    
    return { 'success': True, 'embedding': embedding }