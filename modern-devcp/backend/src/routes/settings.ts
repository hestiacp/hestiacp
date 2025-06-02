import { Router } from 'express'

const router = Router()

// GET /api/settings
router.get('/', (req, res) => {
  res.json({
    message: 'Setting endpoint',
    data: [],
    total: 0,
  })
})

// POST /api/settings
router.post('/', (req, res) => {
  res.status(201).json({
    message: 'Setting created successfully',
    data: req.body,
  })
})

// GET /api/settings/:id
router.get('/:id', (req, res) => {
  res.json({
    message: 'Setting details',
    data: { id: req.params.id },
  })
})

// PUT /api/settings/:id
router.put('/:id', (req, res) => {
  res.json({
    message: 'Setting updated successfully',
    data: { id: req.params.id, ...req.body },
  })
})

// DELETE /api/settings/:id
router.delete('/:id', (req, res) => {
  res.json({
    message: 'Setting deleted successfully',
    id: req.params.id,
  })
})

export default router
