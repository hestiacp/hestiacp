import { Router } from 'express'

const router = Router()

// GET /api/system
router.get('/', (req, res) => {
  res.json({
    message: 'System endpoint',
    data: [],
    total: 0,
  })
})

// POST /api/system
router.post('/', (req, res) => {
  res.status(201).json({
    message: 'System created successfully',
    data: req.body,
  })
})

// GET /api/system/:id
router.get('/:id', (req, res) => {
  res.json({
    message: 'System details',
    data: { id: req.params.id },
  })
})

// PUT /api/system/:id
router.put('/:id', (req, res) => {
  res.json({
    message: 'System updated successfully',
    data: { id: req.params.id, ...req.body },
  })
})

// DELETE /api/system/:id
router.delete('/:id', (req, res) => {
  res.json({
    message: 'System deleted successfully',
    id: req.params.id,
  })
})

export default router
