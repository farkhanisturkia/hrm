import cv2
import numpy as np
from insightface.app import FaceAnalysis

known_faces = {}

def initialize_model():
    model = FaceAnalysis(name='buffalo_l', providers=['CPUExecutionProvider'])
    model.prepare(ctx_id=-1)

    return model

model_app = initialize_model()

def get_face_embedding(image_bytes):
    nparr = np.frombuffer(image_bytes, np.uint8)
    image = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

    if image is None:
        return None
    
    faces = model_app.get(image)
    if not faces:
        return None
    
    return faces[0].normed_embedding.tolist()

def compare_faces(target_embedding, known_users, threshold=0.5):
    best_match = None
    highest_similarity = -1
    
    target_vec = np.array(target_embedding, dtype=np.float32)

    for user in known_users:
        if user['embedding'] is None:
            continue
            
        known_vec = np.array(user['embedding'], dtype=np.float32)
        
        if target_vec.shape != known_vec.shape:
            print(f"Dimensi tidak cocok untuk user {user['id']}")
            continue

        similarity = np.dot(target_vec, known_vec)

        if similarity > highest_similarity:
            highest_similarity = similarity
            best_match = user['id']

    if highest_similarity > threshold:
        return best_match, float(highest_similarity)
    
    return None, float(highest_similarity)