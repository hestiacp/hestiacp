import { Router } from 'express'

const router = Router()

// GET /api/files
router.get('/', (req, res) => {
  res.json({
    message: 'File endpoint',
    data: [],
    total: 0,
  })
})

// POST /api/files
router.post('/', (req, res) => {
  res.status(201).json({
    message: 'File created successfully',
    data: req.body,
  })
})

// GET /api/files/:id
router.get('/:id', (req, res) => {
  res.json({
    message: 'File details',
    data: { id: req.params.id },
  })
})

// PUT /api/files/:id
router.put('/:id', (req, res) => {
  res.json({
    message: 'File updated successfully',
    data: { id: req.params.id, ...req.body },
  })
})

// DELETE /api/files/:id
router.delete('/:id', (req, res) => {
  res.json({
    message: 'File deleted successfully',
    id: req.params.id,
  })
})

export default router
