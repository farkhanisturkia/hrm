FROM python:3.13.12-bookworm

WORKDIR /app

RUN apt-get update && apt-get install -y \
    build-essential \
    g++ \
    gcc \
    make \
    cmake \
    libgl1 \
    libglib2.0-0 \
    && rm -rf /var/lib/apt/lists/*

RUN pip install --upgrade pip
RUN pip install \
    fastapi \
    uvicorn \
    python-multipart \
    onnxruntime \
    opencv-python \
    insightface

COPY . .

EXPOSE 8000

CMD [ "uvicorn", "main:app", "--app-dir", "src", "--host", "0.0.0.0", "--port", "8000"]